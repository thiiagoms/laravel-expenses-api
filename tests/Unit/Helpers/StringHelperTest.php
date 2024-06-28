<?php

dataset('clean provider', fn (): array => [
    'should remove spaces and html tags from string' => [
        'expectedInput' => ' <h1>Hello World</h1> ',
        'expectedOutput' => 'Hello World',
    ],
    'should remove spaces and html tags from each element of array' => [
        'expectedInput' => [
            ' Hello World ',
            ' <script>console.log("Hello World")</script> ',
        ],
        'expectedOutput' => [
            'Hello World',
            'console.log("Hello World")',
        ],
    ],
    'should return empty array if input is empty' => [
        'expectedInput' => [],
        'expectedOutput' => [],
    ],
]);

test(
    'clean helper',
    fn (string|array $expectedInput, string|array $expectedOutput) => expect(clean($expectedInput))->toBe($expectedOutput)
)->with('clean provider');

dataset('isEmail provider', fn (): array => [
    'should return true if email is valid' => [
        'expectedInput' => fake()->freeEmail(),
        'expectedOutput' => true,
    ],
    'should return false if email is not valid' => [
        'expectedInput' => fake()->name(),
        'expectedOutput' => false,
    ],
    'should return false if email is empty' => [
        'expectedInput' => '',
        'expectedOutput' => false,
    ],
]);

test(
    'isEmail helper',
    fn (string $expectedInput, bool $expectedOutput) => expect(isEmail($expectedInput))->toBe($expectedOutput)
)->with('isEmail provider');
