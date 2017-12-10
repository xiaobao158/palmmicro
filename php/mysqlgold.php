<?php

function GoldEtfGetSymbolArray()
{
    return array('sh518800', 'sh518880', 'sz159934', 'sz159937', 'sz161226'); 
}

function in_arrayGoldEtf($strSymbol)
{
    return in_array(strtolower($strSymbol), GoldEtfGetSymbolArray());
}

function GoldEtfGetCnFutureSymbol($strSymbol)
{
    if ($strSymbol == 'SZ161226')   return 'AG0';
    return 'AU0';
}

function GoldEtfGetFutureSymbol($strSymbol)
{
    if ($strSymbol == 'SZ161226')   return 'SI';
    return 'GC';
}

function GoldEtfGetAllSymbolArray($strSymbol)
{
    $strCnFutureSymbol = GoldEtfGetCnFutureSymbol($strSymbol);
    $strFutureSymbol = GoldEtfGetFutureSymbol($strSymbol);
    return array($strSymbol, FutureGetSinaSymbol($strCnFutureSymbol), FutureGetSinaSymbol($strFutureSymbol));
}

class MyGoldEtfReference extends MyFundReference
{
    // constructor 
    function MyGoldEtfReference($strSymbol)
    {
        parent::MyFundReference($strSymbol);
        $this->SetForex('USCNY');
        $this->est_ref = new MyFutureReference(GoldEtfGetCnFutureSymbol($strSymbol));
        $this->future_ref = new MyFutureReference(GoldEtfGetFutureSymbol($strSymbol));
        $this->EstNetValue();
    }

    function _estGoldEtf($fEst)
    {
        $fVal = $fEst / $this->fFactor;
        return $this->AdjustPosition($fVal); 
    }
    
    function EstNetValue()
    {
        $this->AdjustFactor();
        
        $this->fPrice = $this->_estGoldEtf($this->est_ref->fPrice);
        $this->strOfficialDate = $this->est_ref->strDate;
        $this->UpdateEstNetValue();

        $this->EstRealtimeNetValue();
    }

    function EstRealtimeNetValue()
    {
        $this->est_ref->LoadFutureFactor($this->future_ref, $this->strForexSqlId);
        $fEst = $this->est_ref->EstByFuture($this->future_ref->fPrice, $this->GetForexNow());
        $this->fRealtimeNetValue = $this->_estGoldEtf($fEst);
    }

    function AdjustFactor()
    {
        if ($this->UpdateOfficialNetValue())
        {
            $est_ref = $this->est_ref;
            if ($est_ref->bHasData == false)            return false;
            if ($this->strDate != $est_ref->strDate)    return false;
            
            $iHour = intval(substr($est_ref->strTime, 0, 2));
            if ($iHour >= 9 && $iHour <= 15)
            {
                $this->fFactor = $est_ref->fPrice / $this->fPrevPrice;
                $this->InsertFundCalibration($est_ref, $est_ref->strPrice);
            }
            else
            {
                $this->fFactor = $est_ref->fPrevPrice / $this->fPrevPrice;
                $this->InsertFundCalibration($est_ref, $est_ref->strPrevPrice);
            }
            return $this->fFactor;
        }
        return false;
    }
}

?>
