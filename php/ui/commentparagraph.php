<?php

define('MAX_COMMENT_DISPLAY', 5);

function EchoCommentLinkParagraph($str, $strQuery, $bChinese)
{
    $str = "<font color=green>$str</font>";
    $str .= ' '.GetAllCommentLink($strQuery, $bChinese);
    EchoParagraph($str);
}

class CommentAccount extends TitleAccount
{
	var $comment_sql;
	
    function CommentAccount($strQueryItem = false, $arLoginTitle = false) 
    {
        parent::TitleAccount($strQueryItem, $arLoginTitle);
        $this->comment_sql = new PageCommentSql();
    }
    
    function GetCommentSql()
    {
    	return $this->comment_sql;
    }
    
    function GetCommentDescription($record, $strWhere, $bChinese)
    {
    	$strTime = $record['date'].' '.$record['time'];
    	$strAuthor = GetMemberLink($record['member_id'], $bChinese);

		$ip_sql = $this->GetIpSql();
    	$strIp = GetIpLink($ip_sql->GetIp($record['ip_id']), $bChinese);
    
//		$sql = new PageSql();
		$sql = $this->GetPageSql();
		$strUri = $sql->GetKey($record['page_id']);
	
		$strTimeLink = "<a href=\"$strUri#{$record['id']}\">$strTime</a>";
		if (strpos($strWhere, 'page_id') !== false)
		{
			$strTimeLink = "<b><a name=\"{$record['id']}\">$strTime</a></b>";
		}
		else if (strpos($strWhere, 'member_id') !== false)
		{
			$strAuthor = '';
		}
		else if (strpos($strWhere, 'ip_id') !== false)
		{
			$strIp = '';
		}
    
		return "$strAuthor $strTimeLink $strIp";
	}

    function _echoSingleComment($record, $strWhere, $bChinese)
    {
    	$strEdit = '';
    	$strDelete = '';
    	if ($this->IsReadOnly() == false)
    	{
    		$strMemberId = $this->GetLoginId();
    		if ($record['member_id'] == $strMemberId)
    		{	// I posted the comment
    			$strEdit = GetEditLink('/account/editcomment', $record['id'], $bChinese);
    		}

    		// <a href="delete.page" onclick="return confirm('Are you sure you want to delete?')">Delete</a> 
    		if ($this->IsAdmin() || $record['member_id'] == $strMemberId)
    		{
    			$strDelete = GetDeleteLink('/account/php/_submitcomment.php?delete='.$record['id'], '评论', 'comment', $bChinese);
    		}
    	}

    	$strDescription = $this->GetCommentDescription($record, $strWhere, $bChinese);
    	$strComment = nl2br($record['comment']);
	
    	echo <<<END
	<p>$strDescription $strDelete $strEdit 
        <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="comment{$record['id']}">
        <tr>
            <td class=c1 width=640 align=center>$strComment</td>
        </tr>
        </TABLE>
	</p>
END;
	}

	function EchoComments($strWhere, $iStart, $iNum, $bChinese)
    {
    	if ($result = $this->comment_sql->GetAll($strWhere, $iStart, $iNum)) 
    	{
    		while ($record = mysql_fetch_assoc($result)) 
    		{
    			$this->_echoSingleComment($record, $strWhere, $bChinese);
    		}
    		@mysql_free_result($result);
    	}
    }

	function CountComments($strWhere)
    {
		return $this->comment_sql->CountData($strWhere);
	}
}

?>
