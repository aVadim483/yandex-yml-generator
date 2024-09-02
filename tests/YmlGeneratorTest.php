<?php

declare(strict_types=1);

namespace avadim\YandexYmlGenerator;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/autoload.php';

final class YmlGeneratorTest extends TestCase
{
    public function testValidator()
    {
        $fileName = __DIR__ . '/test.yml';
        $yml = new YmlDocument();
        $yml->fileName($fileName);

        $elements = [
            'name' => 'required|string:1,120|alpha_digits',
            'int' => 'int|max:10|min:-8',
            'bool' => 'bool',
            'string' => 'string|numeric',
        ];
        $offer = $yml->offer(1, $elements);
        foreach ($elements as $name => $rule) {
            $offer->appendNode($name);
        }

        $yml->saveAndClose();

        $this->assertTrue(is_file($fileName));
    }

}

