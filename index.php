
<?
// Выбирает 5 случайных элементов из информационной системы, имеющих хотя бы один общий тег с исходным и добавляет такие элементы контроллеру в тег samenews.
// Код вносится в типовую дин. страницу ИС перед показом контроллера.

$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);
$oTag_Informationsystem_Items = $oInformationsystem_Item->Tag_Informationsystem_Items->findAll();

// Минимальное количество тегов для совпадения.
$iSameTags = 2;

$aTagIds = array();
foreach($oTag_Informationsystem_Items as $oTag_Informationsystem_Item)
{
   $aTagIds[] = $oTag_Informationsystem_Item->tag_id;
}

if (count($aTagIds))
{
   $oSameTag_Informationsystem_Items = Core_Entity::factory('Tag_Informationsystem_Item');
   $oSameTag_Informationsystem_Items->queryBuilder()
      ->select('tag_informationsystem_items.*')
      ->where('tag_id', 'IN', $aTagIds)
      ->where('tag_informationsystem_items.informationsystem_item_id', '!=', $oInformationsystem_Item->id)
      ->join('informationsystem_items', 'tag_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
      ->join('informationsystems', 'informationsystem_items.informationsystem_id', '=', 'informationsystems.id')
      ->where('informationsystems.site_id', '=', CURRENT_SITE)
	  ->where('informationsystem_items.deleted', '=', 0)
      ->groupBy('informationsystem_items.id')
      ->having('COUNT(tag_id)', '>=', $iSameTags)
      ->clearOrderBy()
      ->orderBy('RAND()')
      ->limit(5);

   $aSameTag_Informationsystem_Items = $oSameTag_Informationsystem_Items->findAll();

   $oXmlSamenews = Core::factory('Core_Xml_Entity')->name('samenews');
   $Informationsystem_Controller_Show->addEntity($oXmlSamenews);

   foreach($aSameTag_Informationsystem_Items as $oSameTag_Informationsystem_Item)
   {
      $oXmlSamenews->addEntity(
         $oSameTag_Informationsystem_Item->Informationsystem_Item->clearEntities()
      );
   }
}
?>

в $iSameTags указывается минимальное количество тегов для совпадения.

// Далее вызов в темплейте
<div class="clearfix">
	<xsl:if test="count(/informationsystem/samenews/informationsystem_item) &gt; 0">
		<h2>Новости по теме</h2> <br />
		<xsl:apply-templates select="/informationsystem/samenews/informationsystem_item"/>
	</xsl:if>
</div>
// И сам темплейт:
<xsl:template match="/informationsystem/samenews/informationsystem_item">
	<div class="news-page-item">
		<a href="{url}" class="news-title"><xsl:value-of disable-output-escaping="yes" select="name"/></a>
	</div>
</xsl:template>