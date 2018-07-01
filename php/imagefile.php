<?php

// ****************************** ImageFile class  *******************************************************
class ImageFile
{
    var $strPathName;
    var $iWidth;
    var $iHeight;
    
    var $image;
    
    var $textcolor;
    var $pixelcolor;
    var $dashedcolor;
    var $linecolor;
    var $comparecolor;
    
    var $strLineColor;
    var $strCompareColor;
    
    var $iFont;
    var $fFontSize;
    var $fFontAngle;
    var $strFontFile;
    
    function ImageFile($strPathName, $iWidth, $iHeight) 
    {
        $this->strPathName = $strPathName;
        $this->iWidth = $iWidth;
        $this->iHeight = $iHeight;
        $this->image = imagecreatetruecolor($iWidth, $iHeight);
        
        $this->textcolor = imagecolorallocate($this->image, 255, 255, 255);
        $this->pixelcolor = imagecolorallocate($this->image, 0, 255, 255);
        $this->dashedcolor = imagecolorallocate($this->image, 255, 0, 255);
        $this->_set_line_color(255, 0, 0);
        $this->_set_compare_color(0, 255, 0);
        
        $this->iFont = 5;
        $this->fFontSize = 15.0;
        $this->fFontAngle = 0.0;
        $this->strFontFile = DebugGetFontName('simkai');
    }
 
    function _getColorString($r, $g, $b)
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    function _set_line_color($r, $g, $b)
    {
        $this->linecolor = imagecolorallocate($this->image, $r, $g, $b);
        $this->strLineColor = $this->_getColorString($r, $g, $b);
    }
    
    function _set_compare_color($r, $g, $b)
    {
        $this->comparecolor = imagecolorallocate($this->image, $r, $g, $b);
        $this->strCompareColor = $this->_getColorString($r, $g, $b);
    }
    
    function Text($x, $y, $strText)
    {
//        imagestring($this->image, $this->iFont, $x, $y, $strText, $this->textcolor);
		$ar = imagettfbbox($this->fFontSize, $this->fFontAngle, $this->strFontFile, $strText);
		$iMin = min($ar[0], $ar[6]);
		if ($x + $iMin < 0)					$x = 1 - $iMin;
		$iMax = max($ar[2], $ar[4]);
		if ($x + $iMax > $this->iWidth)		$x = $this->iWidth - $iMax - 2;
		$iMin = min($ar[5], $ar[7]);
		if ($y + $iMin < 0)					$y = 1 - $iMin;
		$iMax = max($ar[3], $ar[1]);
		if ($y + $iMax > $this->iHeight)		$y = $this->iHeight - $iMax - 2;
		return imagettftext($this->image, $this->fFontSize, $this->fFontAngle, $x, $y, $this->textcolor, $this->strFontFile, $strText);
    }
    
    function _line($x1, $y1, $x, $y, $color)
    {
    	imageline($this->image, $x1, $y1, $x, $y, $color);
    }
    
    function Line($x1, $y1, $x, $y)
    {
    	$this->_line($x1, $y1, $x, $y, $this->linecolor);
    }
    
    function CompareLine($x1, $y1, $x, $y)
    {
    	$this->_line($x1, $y1, $x, $y, $this->comparecolor);
    }
    
    function DashedLine($x1, $y1, $x, $y)
    {
    	$arStyle = array($this->dashedcolor, $this->dashedcolor, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
    	imagesetstyle($this->image, $arStyle);
    	imageline($this->image, $x1, $y1, $x, $y, IMG_COLOR_STYLED);
    }
    
 	function Pixel($x, $y)
    {
    	imagesetpixel($this->image, $x, $y, $this->pixelcolor);
    }
    
    function SaveFile()
    {
//    	unlinkEmptyFile($this->strPathName);
        imagejpeg($this->image, $this->strPathName);  
        imagedestroy($this->image);
    }
    
    function GetPathName()
    {
    	return $this->strPathName;
    }
}

class PageImageFile extends ImageFile
{
	var $iBottom;
	var $iTextHeight;
	
    function PageImageFile() 
    {
        parent::ImageFile(DebugGetImageName(UrlGetUniqueString()), 640, 480);
    }
    
    function _textDateVal($x, $y, $strDate, $fVal)
    {
		return $this->Text($x, $y, substr($strDate, 2).' '.strval_round($fVal));
    }
    
    function _getVertialPos($fVal, $fMax, $fMin)
    {
		return intval(($this->iBottom - $this->iTextHeight) * ($fVal - $fMax) / ($fMin - $fMax)) + $this->iTextHeight;
    }
    
    function _drawDashedLine($fVal, $fMax, $fMin)
    {
    	if ($fMax > $fVal && $fMin < $fVal)
    	{
    		$y = $this->_getVertialPos($fVal, $fMax, $fMin);
    		$this->DashedLine(0, $y, $this->iWidth, $y);
    		return true;
    	}
    	return false;
    }
    
    function _drawDashedGrid($fMax, $fMin)
    {
    	$iStep = 80;
    	for ($x = $iStep; $x < $this->iWidth; $x += $iStep)
    	{
    		$this->DashedLine($x, 0, $x, $this->iHeight);
    	}
    	$this->_drawDashedLine(0.0, $fMax, $fMin);
   		$this->_drawDashedLine(1.0, $fMax, $fMin);
   		$this->_drawDashedLine(-1.0, $fMax, $fMin);
    }
    
    function DrawDateArray($ar)
    {
    	$ar = array_reverse($ar);	// ksort($ar);
    	reset($ar);
    	$this->_textDateVal(0, $this->iHeight, key($ar), current($ar));
    	end($ar);
    	$arPos = $this->_textDateVal($this->iWidth, $this->iHeight, key($ar), current($ar));
    	$this->iBottom = min($arPos[5], $arPos[7]);
    	$this->iTextHeight = $this->iHeight - $this->iBottom + 1;
    	
    	$fMax = max($ar);
    	$fMin = min($ar);
    	$this->iBottom -= $this->iTextHeight;
    	$this->_drawDashedGrid($fMax, $fMin);

    	$iCount = count($ar);
    	$iCur = 0;
    	$iMaxPos = false;
    	$iMinPos = false;
    	foreach ($ar as $strDate => $fVal)
    	{
    		$x = intval($this->iWidth * $iCur / $iCount);                                                                 
    		$y = $this->_getVertialPos($fVal, $fMax, $fMin);
   			if ($iMaxPos == false && abs($fVal - $fMax) < 0.000001)
    		{
   				$this->_textDateVal($x, 0, $strDate, $fVal);
   				$iMaxPos = $iCur;
    		}
    		if ($iMinPos == false && abs($fVal - $fMin) < 0.000001)
    		{
   				$this->_textDateVal($x, $this->iBottom, $strDate, $fVal);
   				$iMinPos = $iCur;
    		}
    		
   			if ($iCur != 0)
   			{
   				$this->Line($x1, $y1, $x, $y);
   			}
   			$x1 = $x;
   			$y1 = $y;
    		$iCur ++;
    	}
    }

    function DrawCompareArray($ar)
    {
    	$ar = array_reverse($ar);
    	$fMax = max($ar);
    	$iCount = count($ar);
    	$iCur = 0;
    	foreach ($ar as $strDate => $fVal)
    	{
    		$x = intval($this->iWidth * $iCur / $iCount);                                                                 
    		$y = $this->_getVertialPos($fVal, $fMax, 0.0);
   			if ($iCur != 0)
   			{
   				$this->CompareLine($x1, $y1, $x, $y);
   			}
   			$x1 = $x;
   			$y1 = $y;
    		$iCur ++;
    	}
    }

    function Show($strName, $strCompare, $strCsv)
    {
    	$this->SaveFile();
    	$strRand = strval(rand());
    	$strCsvLink = GetFileLink($strCsv);
    	echo <<< END
    		<p><font color={$this->strLineColor}>$strName</font> <font color={$this->strCompareColor}>$strCompare</font> $strCsvLink
    		<br /><img src={$this->strPathName}?$strRand alt="$strRand automatical generated image, do NOT link" />
    		</p>
END;
	}
}

?>
