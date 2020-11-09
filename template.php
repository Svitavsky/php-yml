<\?xml version="1.0" encoding="UTF-8"?>
<yml_catalog date="<?= $date ?>">
    <shop>
        <name><?= $companyName ?></name>
        <company><?= $companyDescription ?></company>
        <url><?= $companyWebsite ?></url>
        <currencies>
            <?php foreach ($currencies as $code => $rate): ?>
                <currency id="<?= $code ?>" rate="<?= $rate ?>"/>
            <?php endforeach; ?>
        </currencies>
        <categories>
            <?php foreach ($categories as $category): ?>
                <?php if (!is_int($category['id']) || $category['id'] <= 0): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php if (!is_int($category['parentId']) || $category['parentId'] <= 0): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php $parentId = isset($category['parentId']) ? "parentId=\"{$category['parentId']}\"" : '' ?>
                <category id="<?= $category['id'] ?>" <?= $parentId ?>><?= $category['name'] ?></category>
            <?php endforeach; ?>
        </categories>
        <offers>
            <?php foreach ($offers as $offer): ?>
                <offer id="<?= $offer['id'] ?>" <?= isset($offer['bid']) ? "bid=\"\"" : '' ?>>
                    <name><?= $offer['name'] ?></name>
                    <?php if (isset($offer['vendor'])): ?>
                        <vendor><?= $offer['vendor'] ?></vendor>
                    <?php endif; ?>
                    <?php if (isset($offer['vendorCode'])): ?>
                        <vendorCode><?= $offer['vendorCode'] ?></vendorCode>
                    <?php endif; ?>
                    <url><?= $offer['url'] ?></url>
                    <price><?= $offer['price'] ?></price>
                    <?php if (isset($offer['oldprice'])): ?>
                        <oldprice><?= $offer['oldprice'] ?></oldprice>
                    <?php endif; ?>
                    <enable_auto_discounts><?= $offer['enable_auto_discounts'] ?></enable_auto_discounts>
                    <currencyId><?= $offer['currencyId'] ?></currencyId>
                    <categoryId><?= $offer['categoryId'] ?></categoryId>
                    <?php if (isset($offer['picture']) && strlen($offer['picture']) < 512): ?>
                        <picture><?= $offer['picture'] ?></picture>
                    <?php endif; ?>
                    <?php if (isset($offer['supplier'])): ?>
                        <supplier ogrn="<?= $offer['supplier'] ?>"/>
                    <?php endif; ?>
                    <?php if (isset($offer['delivery'])): ?>
                        <delivery><?= $offer['delivery'] ?></delivery>
                    <?php endif; ?>
                    <?php if (isset($offer['delivery-options']) && is_array($offer['delivery-options'])): ?>
                        <?php foreach ($offer['delivery-options'] as $option): ?>
                            <?php $orderBefore = $option['order-before'] ? "order-before=\"{$option['order-before']}\"" : ''; ?>
                            <option cost="<?= $option['cost'] ?>" days="<?= $option['days'] ?>" <?= $orderBefore ?>/>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($offer['pickup'])): ?>
                        <pickup><?= $offer['pickup'] ?></pickup>
                    <?php endif; ?>
                    <?php if (isset($offer['pickup-options']) && is_array($offer['pickup-options'])): ?>
                        <?php foreach ($offer['pickup-options'] as $option): ?>
                            <?php $orderBefore = $option['order-before'] ? "order-before=\"{$option['order-before']}\"" : ''; ?>
                            <option cost="<?= $option['cost'] ?>" days="<?= $option['days'] ?>" <?= $orderBefore ?>/>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($offer['store'])): ?>
                        <store><?= $offer['store'] ?></store>
                    <?php endif; ?>
                    <description><?= $offer['description'] ?></description>
                    <!--                    <sales_notes>--><? //= $offer['sales_notes'] ?><!--</sales_notes>-->
                    <!--                    <min-quantity>2</min-quantity>-->
                    <?php if (isset($offer['manufacturer_warranty'])): ?>
                        <manufacturer_warranty><?= $offer['manufacturer_warranty'] ?></manufacturer_warranty>
                    <?php endif; ?>
                    <?php if (isset($offer['country_of_origin']) && in_array($offer['country_of_origin'], $availableCountries)): ?>
                        <country_of_origin><?= $offer['country_of_origin'] ?></country_of_origin>
                    <?php endif; ?>
                    <?php if (isset($offer['adult'])): ?>
                        <adult><?= $offer['adult'] ?></adult>
                    <?php endif; ?>
                    <?php if (isset($offer['barcode']) && is_array($offer['barcode'])): ?>
                        <?php foreach ($offer['barcode'] as $barcode): ?>
                            <barcode><?= $offer['barcode'] ?></barcode>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($offer['param']) && is_array($offer['param'])): ?>
                        <?php foreach ($offer['param'] as $param): ?>
                            <?php $unit = $param['unit'] ? "order-before=\"{$param['unit']}\"" : ''; ?>
                            <param name="<?= $param['name'] ?>" <?= $unit ?>><?= $param['value'] ?></param>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (isset($offer['condition'], $offer['condition_description']) &&
                        in_array($offer['condition'], self::CONDITION_TYPES) &&
                        strlen($offer['condition_description']) <= self::CONDITION_DESCRIPTION_LENGTH): ?>
                        <condition type="<?= $offer['condition'] ?>">
                            <reason><?= $offer['condition_description'] ?></reason>
                        </condition>
                    <?php endif; ?>
                    <?php if (isset($offer['credit-template'])): ?>
                        <credit-template id="<?= $offer['credit-template'] ?>"/>
                    <?php endif; ?>
                    <?php if (isset($offer['expiry'])): ?>
                        <expiry><?= $offer['expiry'] ?></expiry>
                    <?php endif; ?>
                    <?php if (isset($offer['weight'])): ?>
                        <weight><?= $offer['weight'] ?></weight>
                    <?php endif; ?>
                    <?php if (isset($offer['dimensions'])): ?>
                        <dimensions><?= $offer['dimensions'] ?></dimensions>
                    <?php endif; ?>
                    <?php if (isset($offer['downloadable'])): ?>
                        <downloadable><?= $offer['downloadable'] ?></downloadable>
                    <?php endif; ?>
                    <?php if (isset($offer['age'])): ?>
                        <?php if ($offer['age_type'] === 'year' && in_array($offer['age_type'], self::AGE_YEAR)): ?>
                            <param unit="year"><?= $offer['age'] ?></param>
                        <?php endif; ?>
                        <?php if ($offer['age_type'] === 'month' && in_array($offer['age_type'], self::AGE_MONTH)): ?>
                            <param unit="month"><?= $offer['age'] ?></param>
                        <?php endif; ?>
                    <?php endif; ?>
                </offer>
            <?php endforeach; ?>
        </offers>
    </shop>
</yml_catalog>