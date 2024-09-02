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
 * @method $this origin($arg)
 * @method $this warranty($arg)
 * @method $this sale($arg)
 * @method $this sales_notes($arg)
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
class YmlOffer extends YmlOfferAbstract
{
    protected array $childNodes = [
        'url' => 'url|size:1,512',
        'oldprice' => 'int|min:1',
    ];

    public function attrAvailable(bool $val)
    {
        $this->setAttributeBool('available', $val);

        return $this;
    }

    public function attrBid(int $bid)
    {
        $this->setAttribute('bid', $bid);

        return $this;
    }

    public function attrCbid(int $cbid)
    {
        $this->setAttribute('cbid', $cbid);

        return $this;
    }

    public function attrFee(int $fee)
    {
        $this->setAttribute('fee', $fee);

        return $this;
    }

    public function model(string $val)
    {
        $this->appendNode('model', $val);

        return $this;
    }


    public function vendor(string $val)
    {
        $this->appendNode('vendor', $val);

        return $this;
    }


    public function vendorCode(string $val)
    {
        $this->appendNode('vendorCode', $val);

        return $this;
    }


    public function url(string $url)
    {
        $this->appendNode('url', $url);

        return $this;
    }

    /**
     * @param $oldPrice
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function oldPrice(int $oldPrice)
    {
        $this->appendNode('oldprice', $oldPrice);

        return $this;
    }

    public function deliveryOption($cost, $days, $before = -1)
    {
        $options = $this->domElement->getElementsByTagName('delivery-options');

        if (!$options->length) {
            $dlv = new \DomElement('delivery-options');
            $this->domElement->appendChild($dlv);
        }
        else {
            $dlv = $options->item(0);
            $opts = $dlv->getElementsByTagName('option');
            $this->check($opts->length >= 5, "максимум 5 опций доставки");
        }

        $this->check(!is_int($cost) || $cost < 0, "cost должно быть целым и положительным");
        $this->check(preg_match("/[^0-9\-]/", $days), "days должно состоять из цифр и тире");
        $this->check(!is_int($before) || $before > 24, "order-before должно быть целым и меньше 25");

        $opt = new \DomElement('option');
        $dlv->appendChild($opt);

        $opt->setAttribute('cost', $cost);
        $opt->setAttribute('days', $days);

        if ($before >= 0) {
            $opt->setAttribute('order-before', $before);
        }

        return $this;
    }

    /**
     * @param string|null $txt
     * @param bool|null $tags
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function description(?string $txt, ?bool $tags = false): YmlOffer
    {
        if ($txt) {
            $this->validateNode('description', $txt);
            if ($tags) {
                $cdata = new \DOMCdataSection($txt);
                $this->appendNode('description', $cdata);
            }
            else {
                $this->appendNode('description', $txt);
            }
        }

        return $this;
    }


    public function inStock(int $value)
    {
        $list = $this->getElementsByTagName('outlet');
        if ($list->length) {
            $outlet = $list->item(0);
            $outlet->setAttribute('outlet', $value);
        }
        else {
            $outlet = $this->createElement('outlet');
            $outlet->setAttribute('outlet', $value);
            $outlet->setAttribute('id', 1);
            $this->appendNode('outlets');
            $outlets = $this->getElementsByTagName('outlets')->item(0);
            $outlets->appendChild($outlet);
        }
    }

    /**
     * @param bool|null $flag
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function pickup(?bool $flag = false): YmlOffer
    {
        $this->appendNode('pickup', ($flag ? 'true' : 'false'));

        return $this;
    }

    /**
     * @param bool|null $flag
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function store(?bool $flag = false): YmlOffer
    {
        $this->appendNode('store', ($flag ? 'true' : 'false'));

        return $this;
    }

    /**
     * @param float $w
     * @param float $h
     * @param float $d
     * @param string|null $unit
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function dimensions(float $w, float $h, float $d, ?string $unit = null): YmlOffer
    {
        $attrs = [];
        if ($unit) {
            $attrs = ['unit', $unit];
        }
        $val = $this->floatStr($w, 2) . '/' . $this->floatStr($h, 2) . '/' . $this->floatStr($d, 2);
        $this->appendNode('dimensions', $val, $attrs);

        return $this;
    }

    /**
     * @param float $weight
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function weight(float $weight): YmlOffer
    {
        $this->appendNode('weight', $this->floatStr($weight));

        return $this;
    }


    public function _minq($args)
    {
        $this->check(!is_int($args[0]) || $args[0] < 1, "min-quantity должен содержать только цифры");
        return $this->appendNode('min-quantity', $args[0]);
    }

    public function _stepq($args)
    {
        $this->check(!is_int($args[0]) || $args[0] < 1, "step-quantity должен содержать только цифры");
        return $this->appendNode('step-quantity', $args[0]);
    }

    public function _page_extent($args)
    {
        $this->check(!is_int($args[0]), "page_extent должен содержать только цифры");
        $this->check($args[0] < 0, "page_extent должен быть положительным числом");

        return $this->appendNode('page_extent', $args[0]);
    }

    public function _sales_notes($args)
    {
        return $this->addNodeStr('sales_notes', $args[0], 50);
    }

    public function _age($args)
    {
        $this->check(!is_int($args[0]), 'age должен иметь тип int');

        $ageEl = new \DomElement('age', $args[0]);
        $this->appendChild($ageEl);
        $ageEl->setAttribute('unit', $args[1]);

        switch ($args[1]) {
            case 'year':
                $this->check(!in_array($args[0], [0, 6, 12, 16, 18]), 'age при age_unit=year должен быть 0, 6, 12, 16 или 18');
                break;

            case 'month':
                $this->check(($args[0] < 0) || ($args[0] > 12), 'age при age_unit=month должен быть 0<=age<=12');
                break;

            default:
                $this->check(TRUE, 'age unit должен быть month или year');
                break;
        }
        return $this;
    }

    public function _param($args)
    {
        $newEl = new \DomElement('param', $args[1]);
        $this->appendChild($newEl);
        $newEl->setAttribute('name', $args[0]);
        if (isset($args[2])) {
            $newEl->setAttribute('unit', $args[2]);
        }
        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function picture(string $url)
    {
        $pics = $this->domElement->getElementsByTagName('picture');
        //$this->check($pics->length > 10, 'Можно использовать максимум 10 картинок');
        $this->appendNode('picture', $url);

        return $this;
    }

    /**
     * @param array $pictures
     * @return $this
     */
    public function pictures(array $pictures)
    {
        foreach ($pictures as $url) {
            $this->picture($url);
        }

        return $this;
    }


    public function _group_id($args)
    {
        $this->check(!is_int($args[0]), 'group_id должен содержать только цифры');
        $this->check(strlen($args[0]) > 9, 'group_id не должен быть длиннее 9 символов');
        $this->setAttribute('group_id', $args[0]);

        return $this;
    }

    /**
     * @param $barcode
     *
     * @return $this
     *
     * @throws DOMException
     */
    public function barcode($barcode)
    {
        $barcode = trim($barcode);
        $len = strlen($barcode);
        $this->check(!preg_match('/^[0-9]+$/', $barcode), 'barcode должен содержать только цифры');
        $this->check(!($len == 8 || $len == 12 || $len == 13), 'barcode должен содержать 8, 12 или 13 цифр');
        $this->appendNode('barcode', $barcode);

        return $this;
    }

    public function _year($args)
    {
        $this->check(!is_int($args[0]), 'year должен быть int');

        return $this->appendNode('year', $args[0]);
    }


    public function _cpa($args)
    {
        if (!isset($args[0])) {
            $args[0] = TRUE;
        }

        return $this->appendNode('cpa', ($args[0]) ? '1' : '0');
    }


}
