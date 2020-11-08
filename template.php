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
                <offer id="<?= $offer['id'] ?>">
                    <url><?= $offer['url'] ?></url>
                    <price><?= $offer['price'] ?></price>
                    <currencyId><?= $offer['currency'] ?></currencyId>
                    <categoryId><?= $offer['category_id'] ?></categoryId>
                    <picture><?= $offer['picture'] ?></picture>
                    <name><?= $offer['name'] ?></name>
                    <description><?= $offer['description'] ?></description>
                </offer>
            <?php endforeach; ?>
        </offers>
    </shop>
</yml_catalog>