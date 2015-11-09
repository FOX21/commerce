<?php
namespace Craft;

/**
 * Tax zone model.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $countryBased
 * @property bool $default
 *
 * @property Commerce_CountryModel[] $countries
 * @property Commerce_StateModel[] $states
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://craftcommerce.com/license Craft Commerce License Agreement
 * @see       http://craftcommerce.com
 * @package   craft.plugins.commerce.models
 * @since     1.0
 */
class Commerce_TaxZoneModel extends BaseModel
{
    /**
     * @return string
     */
    public function getCpEditUrl()
    {
        return UrlHelper::getCpUrl('commerce/settings/taxzones/' . $this->id);
    }

    /**
     * @return array
     */
    public function getCountriesIds()
    {
        $countries = [];
        foreach($this->getCountries() as $country){
            $countries[] = $country->id;
        }

        return $countries;
    }

    /**
     * @return array
     */
    public function getStatesIds()
    {
        $states = [];
        foreach($this->getStates() as $state){
            $states[] = $state->id;
        }

        return $states;
    }

    /**
     * @return array
     */
    public function getCountriesNames()
    {
        $countries = [];
        foreach($this->getCountries() as $country){
            $countries[] = $country->name;
        }

        return $countries;
    }

    /**
     * @return array
     */
    public function getStatesNames()
    {
        $states = [];
        foreach($this->getStates() as $state){
            $states[] = $state->formatName();
        }

        return $states;
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return [
            'id' => AttributeType::Number,
            'name' => AttributeType::String,
            'description' => AttributeType::String,
            'countryBased' => [AttributeType::Bool, 'default' => 1],
            'default' => [AttributeType::Bool, 'default' => 0],
        ];
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return craft()->commerce_taxZones->getCountriesByTaxZoneId($this->id);
    }

    /**
     * @return array
     */
    public function getStates()
    {
        return craft()->commerce_taxZones->getStatesByTaxZoneId($this->id);
    }
}