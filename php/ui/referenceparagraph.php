<?php
require_once('stocktable.php');

// $ref from StockReference
function _echoReferenceTableItem($ref, $callback = false)
{
   	$strLink = $ref->GetExternalLink();
    if ($ref->HasData())
    {
    	$strPriceDisplay = $ref->GetCurPriceDisplay();
    	$strPercentageDisplay = $ref->GetCurPercentageDisplay();
    	$strDate = $ref->strDate;
    	$strTime = GetTimeHM($ref->strTime);
    }
    else
    {
    	$strPriceDisplay = '';
    	$strPercentageDisplay = '';
    	$strDate = '';
    	$strTime = '';
    }
    
    if ($callback)
    {
        $strDisplayEx = '';
		$arDisplayEx = call_user_func($callback, $ref);
		foreach ($arDisplayEx as $str)
		{
			$strDisplayEx .= GetTableColumnDisplay($str);
		}
    }
    else
    {
		$strDescription = RefGetDescription($ref, true);
        $strDisplayEx = GetTableColumnDisplay($strDescription);
    }

    echo <<<END
    <tr>
        <td class=c1>$strLink</td>
        <td class=c1>$strPriceDisplay</td>
        <td class=c1>$strPercentageDisplay</td>
        <td class=c1>$strDate</td>
        <td class=c1>$strTime</td>
        $strDisplayEx
    </tr>
END;
}

function _echoReferenceTableData($arRef, $callback)
{
    foreach ($arRef as $ref)
    {
    	if ($ref)
    	{
    		_echoReferenceTableItem($ref, $callback);
    		if ($callback == false)
    		{
    			if ($ref->extended_ref)	_echoReferenceTableItem($ref->extended_ref);
    		}
    	}
    }
}

function GetTimeDisplay()
{
    date_default_timezone_set(STOCK_TIME_ZONE_CN);
	$ymd = new NowYMD();
	$strTick = strval($ymd->GetTick() * 1000);
	
	echo <<< END
	<script type="text/javascript">
		var now = new Date($strTick); 
		function UpdateTime() 
		{ 
			now.setTime(now.getTime() + 250); 
			document.getElementById("time").innerHTML = now.toLocaleTimeString(); 
		} 
		setInterval("UpdateTime()", 250);
	</script>
END;

	return '<span id="time"></span>';
}

function EchoReferenceParagraph($arRef, $callback = false, $str = false)
{
	if ($str == false)
	{
        $str = '参考数据 '.GetTimeDisplay();
    }
    
	$arColumn = GetReferenceTableColumn();
	$strId = 'reference';
	if ($callback)
	{
		$strId .= $callback;
		$arColumnEx = call_user_func($callback);
        $strColumnEx = ' ';
		foreach ($arColumnEx as $strColumn)
		{
            $strColumnEx .= GetTableColumn(90, $strColumn);
		}
	}
	else
	{
		$strColumnEx = GetTableColumn(270, $arColumn[5]);
	}
    
    echo <<<END
    	<p>$str
        <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="$strId">
        <tr>
            <td class=c1 width=80 align=center>{$arColumn[0]}</td>
            <td class=c1 width=70 align=center>{$arColumn[1]}</td>
            <td class=c1 width=70 align=center>{$arColumn[2]}</td>
            <td class=c1 width=100 align=center>{$arColumn[3]}</td>
            <td class=c1 width=50 align=center>{$arColumn[4]}</td>
            $strColumnEx
        </tr>
END;

	_echoReferenceTableData($arRef, $callback);
    EchoTableParagraphEnd();
}

?>
