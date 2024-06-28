<?php

dataset('removeEmpty provider', fn (): array => [
    'should remove empty values from array and return new array without empty values' => [
        'expectedInput' => [
            'foo' => 'foo',
            'bar' => 'bar',
            'qux' => '',
        ],
        'expectedOutput' => [
            'foo' => 'foo',
            'bar' => 'bar',
        ],
    ],
    'should return entire array if input array is not empty' => [
        'expectedInput' => [
            'foo' => 'foo',
            'bar' => 'bar',
            'qux' => 'qux',
        ],
        'expectedOutput' => [
            'foo' => 'foo',
            'bar' => 'bar',
            'qux' => 'qux',
        ],
    ],
    'should return empty array if input array is empty' => [
        'expectedInput' => [],
        'expectedOutput' => [],
    ],
]);

test(
    'removeEmpty helper',
    fn (array $expectedInput, array $expectedOutput) => expect(removeEmpty($expectedInput))->toBe($expectedOutput)
)->with('removeEmpty provider');
