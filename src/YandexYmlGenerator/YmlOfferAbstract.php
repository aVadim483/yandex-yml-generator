<?php

namespace avadim\YandexYmlGenerator;

use DOMException;

/**
 * @method $this series($arg)
 * @method $this author($arg)
 * @method $this expiry($arg)
 * @method $this rec($arg)
 * @method $this typePrefix($arg)
 * @method $this countryOfOrigin($arg)
 * @method $this ISBN($arg)
 * @method $this volume($arg)
 * @method $this part($arg)
 * @method $this language($arg)
 * @method $this binding($arg)
 * @method $this tableOfContents($arg)
 * @method $this performedBy($arg)
 * @method $this performanceType($arg)
 * @method $this storage($arg)
 * @method $this format($arg)
 * @method $this recordingLength($arg)
 * @method $this artist($arg)
 * @method $this media($arg)
 * @method $this starring($arg)
 * @method $this director($arg)
 * @method $this originalName($arg)
 * @method $this country($arg)
 * @method $this worldRegion($arg)
 * @method $this region($arg)
 * @method $this dataTour($arg)
 * @method $this hotelStars($arg)
 * @method $this room($arg)
 * @method $this meal($arg)
 * @method $this priceMin($arg)
 * @method $this priceMax($arg)
 * @method $this options($arg)
 * @method $this hall($arg)
 * @method $this hallPart($arg)
 * @method $this isPremiere($arg)
 * @method $this isKids($arg)
 * @method $this vat($arg)
 * @method $this downloadable($arg)
 * @method $this adult($arg)
 * @method $this delivery($arg)
 * @method $this manufacturerWarranty($arg)
 * @method $this pageExtent($arg)
 *
 * @method $this pages($arg)
 * @method $this page_extent($arg)
 * @method $this contents($arg)
 * @method $this performer($arg)
 * @method $this performance($arg)
 * @method $this length($arg)
 * @method $this stars($arg)
 * @method $this premiere($arg)
 * @method $this kids($arg)
 * @method $this cpa($arg)
 *
 */
abstract class YmlOfferAbstract
{
    // parent document
    protected \DOMDocument $domDocument;

    protected \DOMElement $domElement;

    protected string $offerType = '';
    protected string $xmlEncoding;

    // список правила валидации элементов (дочерних узлов)
    protected array $childNodes = [];

    // обязательные узлы
    protected array $required = [];

    // допустимые узлы
    protected array $permitted = [];

    protected bool $permittedOnly = false;

    // правила валидации
    private array $validateRules = [];

    protected array $aliases
        = [
            'origin' => 'country_of_origin', 'warranty' => 'manufacturer_warranty', 'sale' => 'sales_notes',
            'isbn' => 'ISBN', 'pages' => 'page_extent', 'pageExtent' => 'page_extent', 'contents' => 'table_of_contents',
            'performer' => 'performed_by', 'performance' => 'performance_type', 'length' => 'recording_length',
            'stars' => 'hotel_stars', 'premiere' => 'is_premiere', 'kids' => 'is_kids',
        ];


    /**
     * @param \DOMDocument $document
     * @param array $attributes
     * @param array|null $elements
     *
     * @throws DOMException
     */
    public function __construct(\DOMDocument $document, array $attributes, ?array $elements = [])
    {
        $this->domDocument = $document;
        $this->xmlEncoding = $document->xmlEncoding;
        $this->domElement = $document->createElement('offer');

        if (!isset($attributes['id'])) {
            throw new \RuntimeException('Offer attribute "id" required');
        }
        if (!$this->validate($attributes['id'], 'alpha_digits|string:1,20', $error)) {
            throw new \RuntimeException(sprintf('Validation error for offer attribute "id" with value "%s" (%s)', $attributes['id'], $error));
        }
        foreach ($attributes as $name => $value) {
            $this->domElement->setAttribute($name, $value);
        }

        if ($elements) {
            $this->childNodes = $elements;
        }
        if ($this->childNodes) {
            $this->setChildNodes($this->childNodes);
        }
        $p = [
            'simple' => [
                'group_id', 'minq', 'stepq', 'model', 'age', 'vendor', 'vendorCode', 'manufacturer_warranty',
                'downloadable', 'adult', 'rec',
            ],
            'arbitrary' => [
                'group_id', 'minq', 'stepq', 'age', 'vendorCode', 'manufacturer_warranty', 'adult', 'downloadable',
                'typePrefix', 'rec',
            ],
            'book' => [
                'age', 'manufacturer_warranty', 'downloadable', 'author', 'series', 'year', 'ISBN', 'volume', 'part',
                'language', 'binding', 'page_extent', 'minq', 'stepq', 'adult', 'table_of_contents',
            ],
            'audiobook' => [
                'adult', 'manufacturer_warranty', 'minq', 'stepq', 'age', 'downloadable', 'author', 'series', 'year',
                'delivery', 'ISBN', 'volume', 'part', 'language', 'table_of_contents', 'performed_by', 'performance_type',
                'storage', 'format', 'recording_length',
            ],
            'artist' => [
                'minq', 'manufacturer_warranty', 'stepq', 'adult', 'age', 'year', 'media', 'artist', 'downloadable',
                'starring', 'director', 'originalName', 'country',
            ],
            'tour' => [
                'minq', 'stepq', 'manufacturer_warranty', 'age', 'adult', 'country', 'worldRegion', 'region', 'dataTour',
                'hotel_stars', 'room', 'meal', 'price_min', 'price_max', 'downloadable', 'options',
            ],
            'event' => [
                'manufacturer_warranty', 'minq', 'stepq', 'adult', 'age', 'hall', 'hall_part', 'downloadable',
                'is_premiere', 'is_kids',
            ],
            'medicine' => ['vendorCode', 'vendor'],
        ];

        $p_all = [
            'sales_notes', 'country_of_origin', 'barcode', 'cpa', 'param', 'pickup', 'delivery', 'store', 'vat',
            'expiry', 'weight', 'dimensions',
        ]; // методы для всех

        // допустимые элементы
        //$this->permitted = array_merge($p[$offerType], $p_all);

    }

    public function elements(array $elements)
    {
        foreach ($elements as $name => $rules) {
            if (is_int($name) && is_string($rules)) {
                $name = $rules;
                $rules = null;
            }
            if (strpos($rules, '|')) {
                $this->validateRules['_'][$name] = $this->parseRules($rules);
            }
            else {
                $this->validateRules['_'][$name] = [$rules => []];
            }
            if (isset($this->validateRules['_'][$name]['required'])) {
                $this->validateRules['_required'][$name] = true;
            }
            if ($this->permittedOnly) {
                $this->permitted[] = $name;
            }
        }

        return $this;
    }


    protected function setChildNodes(array $childNodes)
    {
        $this->validateRules = [];

        return $this->elements($childNodes);
    }


    public function setAttribute(string $name, string $value)
    {
        $this->domElement->setAttribute($name, $value);

        return $this;
    }

    public function setAttributeBool(string $name, $value)
    {
        return $this->setAttribute($name, $value ? 'true' : 'false');
    }


    /**
     * @param string|array $rules
     *
     * @return array
     */
    protected function parseRules($rules)
    {
        $result = [];
        if (is_string($rules)) {
            $parts = explode('|', $rules);
        }
        foreach ($parts as $part) {
            if (strpos($part, ':')) {
                [$rule, $param] = explode(':', $part, 2);
                if (preg_match('/^(\-?\d+)\s*,\s*(\-?\d+)$/', $param, $matches)) {
                    $result[$rule] = [$matches[1], $matches[2]];
                }
                else {
                    $result[$rule] = [$param];
                }
            }
            else {
                $result[$part] = [];
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @param string|array $rules
     * @param string|null $error
     *
     * @return bool
     */
    public function validate($value, $rules, &$error = null): bool
    {
        $isValid = true;
        if (is_string($rules)) {
            $rules = $this->parseRules($rules);
        }

        if (isset($rules['nullable']) && ($value === null || $value === '')) {
            return true;
        }

        foreach ($rules as $rule => $ruleParams) {
            switch ($rule) {
                case 'int': // int // integer
                case 'integer': // int // integer
                    $isValid = is_int($value);
                    break;
                case 'bool':
                case 'boolean':
                    $isValid = is_bool($value);
                    break;
                case 'float':
                    $isValid = is_float($value);
                    break;
                case 'numeric':
                    $isValid = is_numeric($value);
                    break;

                case 'alpha': // alpha // alpha:ascii
                    if (isset($ruleParams[0]) && $ruleParams[0] === 'ascii') {
                        $isValid = preg_match('/^([a-z])+$/ui', $value);
                    }
                    else {
                        $isValid = preg_match('/^(\p{L}|\p{M})+$/ui', $value);
                    }
                    break;
                case 'alpha_digits': // alpha_num // alpha_num:ascii
                case 'alpha_num': // alpha_num // alpha_num:ascii
                    if (isset($ruleParams[0]) && $ruleParams[0] === 'ascii') {
                        $isValid = preg_match('/^([a-z0-9])+$/ui', $value);
                    }
                    else {
                        $isValid = preg_match('/^(\p{L}|\p{M}|\p{N})+$/ui', $value);
                    }
                    break;
                case 'between': // between:min,max
                    $isValid = ($value >= $ruleParams[0] && $value <= $ruleParams[1]);
                    break;
                case 'cast': // cast:bool // cast:int
                    if ($ruleParams[0] === 'bool') {
                        $isValid = is_bool($value) || strtolower($value) === 'true' || strtolower($value) === 'false'
                            || $value === 0 || $value === 1 || $value === '0' || $value === '1';
                    }
                    elseif ($ruleParams[0] === 'int') {
                        $isValid = preg_match('/^([0-9])+$/', $value);
                    }
                    break;
                case 'decimal': // decimal:len // decimal:min,max
                    $checkValue = str_replace(',', '.', (string)$value);
                    if (strpos($checkValue, '.')) {
                        [$int, $dec] = explode('.', $checkValue, 2);
                    }
                    else {
                        $dec = 0;
                    }
                    if (!empty($ruleParams[1])) {
                        $isValid = strlen($dec) >= $ruleParams[0] && strlen($dec) <= $ruleParams[1];
                    }
                    elseif (!empty($ruleParams[0])) {
                        $isValid = strlen($dec) == $ruleParams[0];
                    }
                    break;
                case 'digits_between': // digits_between:len_min,len_max
                case 'digits': // digits // digits:len // digits:len_min,len_max
                    $isValid = preg_match('/^([0-9])+$/', $value);
                    if (!empty($ruleParams[1])) {
                        $isValid = $isValid && strlen($value) >= $ruleParams[0] && strlen($ruleParams) <= $ruleParams[1];
                    }
                    elseif (!empty($ruleParams[0])) {
                        $isValid = $isValid && strlen($value) == $ruleParams[0];
                    }
                    break;
                case 'email':
                    $isValid = filter_var($value, FILTER_VALIDATE_EMAIL);
                    break;
                case 'max':
                    $isValid = $value <= $ruleParams[0];
                    break;
                case 'min':
                    $isValid = $value >= $ruleParams[0];
                    break;
                case 'regex': // regex:pattern
                    $isValid = preg_match($ruleParams[0], $value);
                    break;
                case 'size': // size // size:len // size:len_min,len_max
                    $checkValue = (string)$value;
                    if (!empty($ruleParams[1])) {
                        $isValid = mb_strlen($checkValue) >= $ruleParams[0] && mb_strlen($checkValue) <= $ruleParams[1];
                    }
                    elseif (!empty($ruleParams[0])) {
                        $isValid = mb_strlen($checkValue) == $ruleParams[0];
                    }
                    break;
                case 'size_max': // size_max:len
                    $checkValue = (string)$value;
                    if (!empty($ruleParams[0])) {
                        $isValid = mb_strlen($checkValue) <= $ruleParams[0];
                    }
                    break;
                case 'string': // string // string:len // string:len_min,len_max
                    $isValid = is_string($value);
                    if (!empty($ruleParams[1])) {
                        $isValid = $isValid && mb_strlen($value) >= $ruleParams[0] && mb_strlen($value) <= $ruleParams[1];
                    }
                    elseif (!empty($ruleParams[0])) {
                        $isValid = $isValid && mb_strlen($value) == $ruleParams[0];
                    }
                    break;
                case 'url':
                    $isValid = filter_var($value, FILTER_VALIDATE_URL);
                    break;
            }
            if (!$isValid) {
                $error = $rule;
                if (isset($ruleParams[0], $ruleParams[1])) {
                    $error .= ':' . $ruleParams[0] . ',' . $ruleParams[1];
                }
                elseif (isset($ruleParams[0])) {
                    $error .= ':' . $ruleParams[0];
                }
                return false;
            }
        }

        return $isValid;
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return void
     */
    protected function validateNode(string $name, $value)
    {
        $isValid = true;
        if (!empty($this->validateRules['_'][$name])) {
            if (!$this->validate($value, $this->validateRules['_'][$name], $error)) {
                throw new \RuntimeException(sprintf('Validation error for offer node "%s" with value "%s" (%s)', $name, $value, $error));
            }
        }
    }

    /**
     * @return string
     */
    public function saveXML(): string
    {
        return $this->domDocument->saveXML($this->domElement);
    }


    /**
     * @param float $val
     * @param int|null $decimals
     *
     * @return string
     */
    protected function floatStr(float $val, ?int $decimals = 3): string
    {
        return number_format($val, $decimals, '.', '');
    }

    /**
     * @param string $name
     * @param $value
     * @param array|null $attrs
     *
     * @return \DomElement
     *
     * @throws DOMException
     */
    public function appendNode(string $name, $value = null, ?array $attrs = []): \DomElement
    {
        if ($this->permittedOnly && !in_array($name, $this->permitted)) {
            throw new \RuntimeException(sprintf('Nom permitted element %s', $name));
        }
        if (!is_object($value)) {
            $this->validateNode($name, $value);
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $node = (($value === null) ? new \DomElement($name) : new \DomElement($name, $value));
            $this->domElement->appendChild($node);
        }
        else {
            $node = new \DomElement($name);
            $this->domElement->appendChild($node);
            $node->appendChild($value);
        }
        if (!empty($attrs)) {
            foreach ($attrs as $k => $v) {
                $node->setAttribute($k, $v);
            }
        }
        return $node;
    }

    /**
     * @param string $name
     * @param $value
     * @param array|null $attrs
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function append(string $name, $value = null, ?array $attrs = [])
    {
        $this->appendNode($name, $value, $attrs);

        return $this;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function __call($method, $args)
    {
        if (preg_match('/^add([A-Z][a-zA-Z0-9_]+)$/', $method, $m)) {
            $method = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $m[1]));
            if (array_key_exists($method, $this->aliases)) {
                $method = $this->aliases[$method];
            }
            $this->appendNode($method, $args[0]);

            return $this;
        }

        return $this;
    }

}
