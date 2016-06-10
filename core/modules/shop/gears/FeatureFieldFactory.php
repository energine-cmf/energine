<?php

namespace Energine\shop\gears;

class FeatureFieldFactory {
    /**
     * Фабричный метод получения экземпляра характеристики по ее feature_id
     *
     * @param int $feature_id
     * @param string|null $value
     * @return FeatureFieldBool|FeatureFieldInt|FeatureFieldOption|FeatureFieldString|FeatureFieldVariant|null
     */
    public static function getField($feature_id, $value = NULL, $productIds = null) {
        $feature_abstract = new FeatureFieldAbstract();
        $feature_abstract->setFeatureId($feature_id);
        $feature_abstract->loadFeatureData();

        if ($data = $feature_abstract->getData()) {
            switch ($feature_abstract->getType()) {

                case FeatureFieldAbstract::FEATURE_TYPE_BOOL:
                    $result = new FeatureFieldBool();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->setValue($value);
                    return $result;
                    break;
                case FeatureFieldAbstract::FEATURE_TYPE_INT:
                    $result = new FeatureFieldInt();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->setValue($value);
                    return $result;
                    break;
                case FeatureFieldAbstract::FEATURE_TYPE_STRING:
                    $result = new FeatureFieldString();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->setValue($value);
                    return $result;
                    break;
                case FeatureFieldAbstract::FEATURE_TYPE_OPTION:
                    $result = new FeatureFieldOption();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->loadFeatureOptions($productIds)
                        ->setValue($value);
                    return $result;
                    break;
                case FeatureFieldAbstract::FEATURE_TYPE_MULTIOPTION:
                    $result = new FeatureFieldMultioption();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->loadFeatureOptions()
                        ->setValue($value);
                    return $result;
                    break;
                case FeatureFieldAbstract::FEATURE_TYPE_VARIANT:
                    $result = new FeatureFieldVariant();
                    $result->setFeatureId($feature_id)
                        ->setData($feature_abstract->getData())
                        ->loadFeatureOptions()
                        ->setValue($value);
                    return $result;
                    break;
            }
        }

        return NULL;
    }
}