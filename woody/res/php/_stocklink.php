<?php

// https://www.jisilu.cn/data/sfnew/detail/502004
function EchoJisiluGradedFund()
{
    $strSymbol = UrlGetTitle();
    $sym = new StockSymbol($strSymbol);
    if ($sym->IsFundA())
    {
        $strHttp = 'https://www.jisilu.cn/data/sfnew/detail/'.$sym->strDigitA;
        $str = DebugGetExternalLink($strHttp, '集思录');
        echo $str;
    }
}

function _GetCombineTransactionLink($strGroupId, $bChinese)
{
    return UrlBuildPhpLink(STOCK_PATH.'combinetransaction', 'groupid='.$strGroupId, '合并记录', 'Combined Records', $bChinese);
}

function _GetReturnGroupLink($strGroupId, $bChinese)
{
    $strLink = SelectGroupInternalLink($strGroupId, $bChinese);
    if ($bChinese)     
    {
        $str = "返回{$strLink}分组 ";
    }
    else
    {
        $str = "Return $strLink Group ";
    }
    return $str;
}

// calibrationhistorycn.php?stockid=76&start=0&num=390
function _GetNavLink($strTitle, $strId, $iTotal, $iStart, $iNum, $bChinese)
{
    return UrlGetNavLink(STOCK_PATH.$strTitle, $strId, $iTotal, $iStart, $iNum, $bChinese);
}

function _GetStockNavLink($strTitle, $strSymbol, $iTotal, $iStart, $iNum, $bChinese)
{
    return _GetNavLink($strTitle, 'symbol='.$strSymbol, $iTotal, $iStart, $iNum, $bChinese);
}

function _GetDevGuideLink($strVer, $bChinese)
{
    if ($strVer != '')
    {
        $strQuery = '#'.$strVer;
    }
    else
    {
        $strQuery = false;
    }
    return UrlBuildPhpLink('/woody/blog/entertainment/20150818', $strQuery, '开发记录', 'Development Record', $bChinese);
}

function _GetAdjustLink($strSymbol, $strQuery, $bChinese)
{
    return UrlBuildPhpLink(STOCK_PATH.'editstockgroup', $strQuery, '校准', 'Adjust', $bChinese).' '.$strSymbol;
}

function _GetPortfolioLink($bChinese)
{
    return UrlBuildPhpLink(STOCK_PATH.'myportfolio', false, '持仓盈亏', 'My Portfolio', $bChinese);
}

function _GetEtfAdjustString($ref, $etf_ref, $bChinese)
{
    $strSymbol = $ref->GetStockSymbol();
    $strQuery = sprintf('%s=%.3f&%s=%.3f', $strSymbol, $ref->fPrice, $etf_ref->GetStockSymbol(), $etf_ref->fPrice);
    return _GetAdjustLink($strSymbol, $strQuery, $bChinese);
}

function _getPersonalGroupLink($strGroupId, $bChinese)
{
    $str = '';
    if ($result = SqlGetStockGroupItemByGroupId($strGroupId)) 
    {   
        while ($groupitem = mysql_fetch_assoc($result)) 
        {
            if (intval($groupitem['record']) > 0)
            {
                $str = SelectGroupInternalLink($strGroupId, $bChinese);
                break;
            }
        }
        @mysql_free_result($result);
    }
    return $str;
}

$group = false;

function _checkPersonalGroupId($strGroupId)
{
    global $group;
    
    if ($group == false)                        return true;
    if ($group->strGroupId != $strGroupId)    return true;
    return false;
}

function _getPersonalLinks($strMemberId, $bChinese)
{
    $str = '<br />';
	if ($result = SqlGetStockGroupByMemberId($strMemberId)) 
	{
		while ($stockgroup = mysql_fetch_assoc($result)) 
		{
		    $strGroupId = $stockgroup['id'];
		    if (_checkPersonalGroupId($strGroupId))
		    {
		        $str .= _getPersonalGroupLink($strGroupId, $bChinese).' ';
		    }
		}
		@mysql_free_result($result);
	}
	return $str;
}

function EchoStockGroupLinks($bChinese)
{
    $ar = GetMenuArray($bChinese);
    $str = '<br />'.UrlGetCategoryLinks(STOCK_PATH, $ar, $bChinese);
    
    $strTitle = UrlGetTitle();
//    if ($strTitle != 'mystockgroup')
    $str .= ' '.StockGetGroupLink($bChinese);
    if ($strTitle != 'myportfolio')    $str .= '<br />'._GetPortfolioLink($bChinese);
    
    if ($strMemberId = AcctIsLogin())
    {
        $str .= _getPersonalLinks($strMemberId, $bChinese);
    }
    echo $str;
}

function _getCategoryArray($bChinese)
{
    if ($bChinese)
    {
        return array('oilfund' => '油气',
                      'commodity' => '商品',
                      'chinainternet' => '海外中国互联网',
                      'qqqfund' => '纳斯达克100',
                      'spyfund' => '标普500',
                      'bricfund' => '金砖四国',
                      'hangseng' => '恒生指数',
                      'hshares' => 'H股国企指数',
                     );
    }
    else
    {
         return array('oilfund' => 'Oil&Gas',
                      'commodity' => 'Commodity',
                      'chinainternet' => 'Overseas China Internet',
                      'qqqfund' => 'NASDAQ-100',
                      'spyfund' => 'S&P 500',
                      'bricfund' => 'BRIC',
                      'hangseng' => 'Hang Seng Index',
                      'hshares' => 'H-Shares',
                     );
    }
}

function _getCategoryLink($strCategory, $bChinese)
{
    $ar = _getCategoryArray($bChinese);
    return UrlGetPhpLink(STOCK_PATH.$strCategory, false, $ar[$strCategory], $bChinese);
}

function EchoStockCategoryLinks($bChinese)
{
    $ar = _getCategoryArray($bChinese);
    $str = '<br />'.UrlGetCategoryLinks(STOCK_PATH, $ar, $bChinese);
    echo $str;
}

function GetCategorySoftwareLinks($arTitle, $strCategory, $bChinese)
{
    $str = '<br />'.$strCategory.' - ';
    foreach ($arTitle as $strTitle)
    {
        if (UrlGetTitle() != $strTitle)
        {
            $strDisplay = StockGetSymbolByTitle($strTitle);
            $str .= UrlGetPhpLink(STOCK_PATH.$strTitle, false, $strDisplay, $bChinese).' ';
        }
    }
    return $str;
}

function _getCategorySoftwareLinks($arTitle, $strCn, $strUs, $bChinese)
{
    return GetCategorySoftwareLinks($arTitle, $bChinese ? $strCn : $strUs, $bChinese);
}

function EchoOilSoftwareLinks($bChinese)
{
    $ar = array('adrptr', 'adrshi', 'adrsnp', 'futurecl', 'futureng', 'futureoil', 'xop');
    $ar = array_merge($ar, LofGetOilEtfSymbolArray());
    $ar = array_merge($ar, LofGetOilSymbolArray());
    $strLink = _getCategoryLink('oilfund', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoGoldSoftwareLinks($bChinese)
{
    $ar = array('futuregc', 'futuresi');
    $ar = array_merge($ar, GoldEtfGetSymbolArray());
    $ar = array_merge($ar, LofGetGoldSymbolArray());
    $strLink = UrlBuildPhpLink(STOCK_PATH.'goldetf', false, '金银', 'Gold and Silver', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoCommoditySoftwareLinks($bChinese)
{
    $ar = LofGetCommoditySymbolArray();
    $strLink = _getCategoryLink('commodity', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoQqqSoftwareLinks($bChinese)
{
    $ar = LofGetQqqSymbolArray(); 
    $strLink = _getCategoryLink('qqqfund', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoSpySoftwareLinks($bChinese)
{
    $ar = array('futurees', 'spy', 'uvxy'); 
    $ar = array_merge($ar, LofGetSpySymbolArray());
    $strLink = _getCategoryLink('spyfund', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoHangSengSoftwareLinks($bChinese)
{
    $ar = array('sz150169');
    $ar = array_merge($ar, LofHkGetHangSengSymbolArray());
    $strLink = _getCategoryLink('hangseng', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoHSharesSoftwareLinks($bChinese)
{
    $ar = array('sz150175');
    $ar = array_merge($ar, LofHkGetHSharesSymbolArray());
    $strLink = _getCategoryLink('hshares', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoASharesSoftwareLinks($bChinese)
{
    $ar = array('sz150022', 'sz150152');
    $str = _getCategorySoftwareLinks($ar, 'A股', 'A Shares', $bChinese);
    echo $str;
}

function EchoBricSoftwareLinks($bChinese)
{
    $ar = LofGetBricSymbolArray();
    $strLink = _getCategoryLink('bricfund', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoChinaInternetSoftwareLinks($bChinese)
{
    $ar = LofGetChinaInternetSymbolArray();
    $strLink = _getCategoryLink('chinainternet', $bChinese);
    echo GetCategorySoftwareLinks($ar, $strLink, $bChinese);
}

function EchoMilitarySoftwareLinks($bChinese)
{
    $ar = array('sh502004', 'sz150181', 'sz150186');
    $str = _getCategorySoftwareLinks($ar, '军工', 'Military', $bChinese);
    echo $str;
}

function EchoBrokageSoftwareLinks($bChinese)
{
    $ar = array('sz150200', 'sz150223');
    $str = _getCategorySoftwareLinks($ar, '证券公司', 'Brokage', $bChinese);
    echo $str;
}

function EchoBoseraSoftwareLinks($bChinese)
{
    $ar = array('sh513500', 'sz159937');
    $strLink = DebugGetExternalLink('http://www.bosera.com', $bChinese ? '博时基金' : 'Bosera Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoBocomSchroderSoftwareLinks($bChinese)
{
    $ar = array('sz164906');
    $strLink = DebugGetExternalLink('http://www.fund001.com', $bChinese ? '交银施罗德基金' : 'BOCOM Schroder Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoChinaAmcSoftwareLinks($bChinese)
{
    $ar = array('sh513660', 'sz159920');
    $strLink = DebugGetExternalLink('http://www.chinaamc.com', $bChinese ? '华夏基金' : 'China AMC');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoCiticPruSoftwareLinks($bChinese)
{
    $ar = array('sz165510', 'sz165513');
    $strLink = DebugGetExternalLink('http://www.citicprufunds.com.cn', $bChinese ? '信诚基金' : 'CITIC-PRUDENTIAL Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoCmfSoftwareLinks($bChinese)
{
    $ar = array('sz150200', 'sz161714');
    $strLink = DebugGetExternalLink('http://www.cmfchina.com/main/index/index.shtml', $bChinese ? '招商基金' : 'China Merchants Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoDaChengSoftwareLinks($bChinese)
{
    $ar = array('sz160924');
    $strLink = DebugGetExternalLink('http://www.dcfund.com.cn', $bChinese ? ' 大成基金' : 'Da Cheng Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoEFundSoftwareLinks($bChinese)
{
    $ar = array('sh502004', 'sh510900', 'sh513050', 'sz159934', 'sz161116', 'sz161125', 'sz161126', 'sz161127', 'sz161128', 'sz161129');
    $strLink = DebugGetExternalLink('http://www.efunds.com.cn', $bChinese ? '易方达基金' : 'E Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoFortuneSoftwareLinks($bChinese)
{
    $ar = array('sh501021', 'sz162411', 'sz162415');
    $strLink = DebugGetExternalLink('http://www.fsfund.com', $bChinese ? '华宝兴业基金' : 'Fortune SG Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoFullgoalSoftwareLinks($bChinese)
{
    $ar = array('sz150152', 'sz150181', 'sz150209', 'sz150223');
    $strLink = DebugGetExternalLink('http://www.fullgoal.com.cn', $bChinese ? '富国基金' : 'Fullgoal Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoGuangFaSoftwareLinks($bChinese)
{
    $ar = array('sz159941', 'sz162719');
    $strLink = DebugGetExternalLink('http://www.gffunds.com.cn', $bChinese ? '广发基金' : 'GF Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoGuoTaiSoftwareLinks($bChinese)
{
    $ar = array('sh513100', 'sh518800', 'sz160216');
    $strLink = DebugGetExternalLink('http://www.gtfund.com', $bChinese ? '国泰基金' : 'GuoTai Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoHarvestSoftwareLinks($bChinese)
{
    $ar = array('sz160717', 'sz160719', 'sz160723');
    $strLink = DebugGetExternalLink('http://www.jsfund.cn', $bChinese ? '嘉实基金' : 'Harvest Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoHuaAnSoftwareLinks($bChinese)
{
    $ar = array('sh513030', 'sh518880', 'sz160416');
    $strLink = DebugGetExternalLink('http://www.huaan.com.cn', $bChinese ? '华安基金' : 'HuaAn Funds');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoPenghuaSoftwareLinks($bChinese)
{
    $ar = array('sh501025', 'sz150205', 'sz150277');
    $strLink = DebugGetExternalLink('http://www.phfund.com.cn', $bChinese ? '鹏华基金' : 'Penghua Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoSouthernSoftwareLinks($bChinese)
{
    $ar = array('sh501018', 'sz160125');
    $strLink = DebugGetExternalLink('http://www.nffund.com', $bChinese ? '南方基金' : 'CSAM');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoSwsMuSoftwareLinks($bChinese)
{
    $ar = array('sz150022', 'sz150186');
    $strLink = DebugGetExternalLink('http://www.swsmu.com', $bChinese ? '申万菱信基金' : 'SWS MU Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoUniversalSoftwareLinks($bChinese)
{
    $ar = array('sz150169', 'sz164701');
    $strLink = DebugGetExternalLink('http://www.99fund.com', $bChinese ? '汇添富基金' : 'CUAM');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

function EchoYinHuaSoftwareLinks($bChinese)
{
    $ar = array('sz150175', 'sz161815');
    $strLink = DebugGetExternalLink('http://www.yhfund.com.cn', $bChinese ? '银华基金' : 'YinHua Fund');
    $str = GetCategorySoftwareLinks($ar, $strLink, $bChinese);
    echo $str;                 
}

?>