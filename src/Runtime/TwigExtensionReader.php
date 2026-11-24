<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Runtime;

/**
 * Reads the Twig functions, filters and tests of extension classes.
 *
 * Extension classes are found in source files below a Twig directory. They are
 * instantiated without their constructor, because getFunctions() and friends
 * only build definition objects. When that fails the names are read from the
 * source text instead.
 */
class TwigExtensionReader
{
    private const KINDS = ['function' => 'Function', 'filter' => 'Filter', 'test' => 'Test'];

    /**
     * @param list<string> $files
     *
     * @return list<array{name: string, kind: string, class: string, callable: string|null, signature: string|null}>
     */
    public function read(array $files): array
    {
        $items = [];

        foreach ($files as $file) {
            if (!str_contains(str_replace('\\', '/', $file), '/Twig/')) {
                continue;
            }

            $contents = @file_get_contents($file);
            if (false === $contents || (!str_contains($contents, 'AbstractExtension') && !str_contains($contents, 'AsTwig'))) {
                continue;
            }

            $class = $this->className($contents);
            if (null === $class) {
                continue;
            }

            foreach ($this->readClass($class, $contents) as $item) {
                $items[$item['kind'].':'.$item['name']] = $item;
            }
        }

        $items = array_values($items);
        usort($items, static fn (array $a, array $b): int => [$a['name'], $a['kind']] <=> [$b['name'], $b['kind']]);

        return $items;
    }

    /**
     * @return list<array{name: string, kind: string, class: string, callable: string|null, signature: string|null}>
     */
    public function readClass(string $class, string $contents): array
    {
        $items = [];

        try {
            if (class_exists($class) && !(new \ReflectionClass($class))->isAbstract()) {
                $items = $this->readByReflection(new \ReflectionClass($class));
            }
        } catch (\Throwable) {
            $items = [];
        }

        if ([] !== $items) {
            return $items;
        }

        return $this->readBySource($class, $contents);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
     * @return list<array{name: string, kind: string, class: string, callable: string|null, signature: string|null}>
     */
    private function readByReflection(\ReflectionClass $reflection): array
    {
        $items = [];
        $class = $reflection->getName();

        if ($reflection->isSubclassOf(\Twig\Extension\AbstractExtension::class) || $reflection->implementsInterface(\Twig\Extension\ExtensionInterface::class)) {
            $instance = $reflection->newInstanceWithoutConstructor();

            foreach (self::KINDS as $kind => $suffix) {
                $getter = 'get'.$suffix.'s';
                if (!method_exists($instance, $getter)) {
                    continue;
                }

                try {
                    $definitions = $instance->{$getter}();
                } catch (\Throwable) {
                    continue;
                }

                if (!\is_array($definitions)) {
                    continue;
                }

                foreach ($definitions as $definition) {
                    if (!\is_object($definition) || !method_exists($definition, 'getName')) {
                        continue;
                    }

                    $callable = method_exists($definition, 'getCallable') ? $definition->getCallable() : null;
                    $items[] = $this->item((string) $definition->getName(), $kind, $class, $callable);
                }
            }
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $kind = match ($attribute->getName()) {
                    \Twig\Attribute\AsTwigFunction::class => 'function',
                    \Twig\Attribute\AsTwigFilter::class => 'filter',
                    \Twig\Attribute\AsTwigTest::class => 'test',
                    default => null,
                };
                if (null === $kind) {
                    continue;
                }

                $arguments = $attribute->getArguments();
                $name = $arguments['name'] ?? $arguments[0] ?? null;
                if (\is_string($name)) {
                    $items[] = $this->item($name, $kind, $class, [$class, $method->getName()]);
                }
            }
        }

        return $items;
    }

    /**
     * @return list<array{name: string, kind: string, class: string, callable: string|null, signature: string|null}>
     */
    private function readBySource(string $class, string $contents): array
    {
        $items = [];

        foreach (self::KINDS as $kind => $suffix) {
            preg_match_all('/new\s+(?:\\\\?Twig\\\\)?Twig'.$suffix.'\(\s*\'([^\']+)\'/', $contents, $matches);
            foreach ($matches[1] as $name) {
                $items[] = $this->item($name, $kind, $class, null);
            }
        }

        return $items;
    }

    /**
     * @return array{name: string, kind: string, class: string, callable: string|null, signature: string|null}
     */
    private function item(string $name, string $kind, string $class, mixed $callable): array
    {
        $callableName = null;
        $signature = null;

        try {
            $reflection = null;
            if (\is_array($callable) && 2 === \count($callable)) {
                $target = $callable[0];
                $callableName = (\is_object($target) ? $target::class : (string) $target).'::'.$callable[1];
                $reflection = new \ReflectionMethod($target, (string) $callable[1]);
            } elseif ($callable instanceof \Closure || \is_string($callable)) {
                $reflection = new \ReflectionFunction($callable);
                $scope = $reflection->getClosureScopeClass();
                $callableName = $scope instanceof \ReflectionClass && !str_contains($reflection->getName(), '{closure')
                    ? $scope->getName().'::'.$reflection->getName()
                    : $reflection->getName();
            }

            if (null !== $reflection) {
                $signature = $this->signature($reflection);
            }
        } catch (\Throwable) {
        }

        return [
            'name' => $name,
            'kind' => $kind,
            'class' => $class,
            'callable' => $callableName,
            'signature' => $signature,
        ];
    }

    private function signature(\ReflectionFunctionAbstract $function): string
    {
        $parameters = [];
        foreach ($function->getParameters() as $parameter) {
            $text = ($parameter->hasType() ? $parameter->getType().' ' : '').($parameter->isVariadic() ? '...' : '').'$'.$parameter->getName();
            if ($parameter->isVariadic()) {
                $parameters[] = $text;
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $text .= ' = '.json_encode($parameter->getDefaultValue());
            }
            $parameters[] = $text;
        }

        $return = $function->hasReturnType() ? ': '.$function->getReturnType() : '';

        return '('.implode(', ', $parameters).')'.$return;
    }

    private function className(string $contents): ?string
    {
        if (1 !== preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/m', $contents, $class)) {
            return null;
        }

        $namespace = 1 === preg_match('/^namespace\s+([^;\s]+);/m', $contents, $match) ? $match[1].'\\' : '';

        return $namespace.$class[1];
    }
}
