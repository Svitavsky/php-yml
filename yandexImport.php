<?php

header('Content-type: application/xml');
header("Content-Type: text/xml; charset=utf-8");

$host = 'localhost';     //������� ������ ��
$user = 'detryer';       //������� ��� ������������
$password = '2303391';   //������� ������
$database = 'opencart';  //������� ��� ���� ������

$mysqli = new mysqli('localhost', 'detryer', '2303391', 'opencart');
if ($mysqli->connect_errno) {
    echo "�� ������� ������������ � MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$mysqli->query("SET NAMES 'utf-8'");
# �������� ������������ XML
# ���������� ��� ���������
$cdate = date("Y-m-d H:i",time());
$csite = "localhost";//������� ����� ��������-��������
$cname = "wdwad";//������� �������� ��������-��������
$cdesc = "wadawd";//������� �������� ��������-��������
#----------------------------------------------
$yandex=<<<END
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="$cdate">
<shop>
<name>$cname</name>
<company>$cdesc</company>
<url>$csite</url>
<currencies>
    <currency id="RUR" rate="1"/>
</currencies>
END;
$arr_cats = array();
#----------------------------------------------
# ��� ������� ������� ��� ������������
$yandex .= "\n\n<categories>\n";
$category = "
                SELECT
                        c.category_id as categoryID, c.parent_id as parent, cd.name as name
                FROM
                        oc_abccategory as c, oc_abccategory_description as cd where c.category_id = cd.category_id AND cd.language_id=1
                ORDER BY
                        parent, name";

$res = $mysqli->query($category);

while ($rezzzz = $res->fetch_assoc()){
    #���������� �����������
    $rezzzz['name']=htmlspecialchars($rezzzz['name']);
    $fftt = "    <category id=\"".$rezzzz['categoryID']."\"";
    if($rezzzz['parent']>0)        $fftt .= " parentId=\"".$rezzzz['parent']."\"";
    $fftt.= ">".$rezzzz['name']."</category>\n";
    $yandex .= $fftt;

    $arr_cats[$rezzzz['categoryID']]=$rezzzz;
}
$yandex .= "</categories>\n";
#----------------------------------------------
$yandex .="\n<offers>\n";

#----- YANDEX ------
$goods = "
        SELECT
                p.product_id AS productID,
                p.price AS price,
                p.status AS in_stock,
                pd.product_id,
                pd.name AS name,
                pd.description AS description,
                pc.category_id AS categoryID,
                pc.product_id,
                i.image as image
        FROM
                 oc_abcproduct AS p,
                 oc_abcproduct_description AS pd,
                 oc_abcproduct_to_category AS pc,
                 oc_abcproduct_image AS i
        WHERE
                p.price>0
                AND pd.product_id=p.product_id
                AND pc.product_id=p.product_id
                AND i.product_id=p.product_id
                AND p.status=1
                AND pd.language_id=1
        ORDER BY name";

$rez = $mysqli->query($goods);
while ($tovar = $rez->fetch_assoc()) {
    $valuta="RUR";//�������� �� ������ ������
    $price=$tovar['price'];
    $price=intval($price);
    $description=htmlspecialchars(strip_tags($tovar['description']));
    $tovar['name'] = htmlspecialchars($tovar['name']);

    #����������
    if ($tovar['image'] != "") {
        $src_file = $csite."image/".$tovar['image'];
        $ppy = "<picture>".$src_file."</picture>";
    } else {
        $ppy = "";
    }
#----------------------------------------------
    $yandex.=<<<END
    <offer id="$tovar[productID]" available="true">
      <url>{$csite}catalog/$tovar[categoryID]-p-$tovar[productID].html</url>
      <price>$price</price>
      <currencyId>$valuta</currencyId>
      <categoryId>$tovar[categoryID]</categoryId>
          $ppy
      <name>$tovar[name]</name>
      <description>$description</description>
    </offer>
END;
}

$yandex .= "</offers>\n</shop>\n</yml_catalog>\n";

#�������, ��� ����������
echo $yandex;

?>
