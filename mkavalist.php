<?php

$fp = fopen("sample.txt", "w");
fwrite($fp, "#norelated\n* タイトル\n\n");
fwrite($fp, "|&attachref(,nolink);|\n|発売日：20xx/xx/xx|\n\n");
fwrite($fp, "** ガチャ\n");

// HTMLひな形
// イベントガチャ
//$url = "http://shop.hangame.co.jp/publicitemlist.nhn?svcid=1188&tabno=1&gender=";
// ONE
//$url = "http://shop.hangame.co.jp/gacha/releaseList.nhn?gccode=ON042K&gender=";
// SP
//$url = "http://shop.hangame.co.jp/gacha/releaseList.nhn?gccode=SP056R&gender=";
// FM+
//$url = "http://shop.hangame.co.jp/gacha/releaseList.nhn?gccode=FP015A&gender=";
// GSG
//$url = "http://shop.hangame.co.jp/gacha/releaseList.nhn?gccode=SG081T&gender=";
// TB
$url = "http://shop.hangame.co.jp/gacha/releaseList.nhn?gccode=TG037B&gender=";

// データ取得
$avaname1 = HTMLget($url."M", 1);
$avaname2 = HTMLget($url."F", 1);
$avasrc1 = HTMLget($url."M", 2);
$avasrc2 = HTMLget($url."F", 2);

// データ整形用変数
$countC =  $countM = $countF = 0;
$listnameC = $listimgC = $listnameM = $listimgM = $listnameF = $listimgF = "|";
$stackC = $stackM = $stackF ="";

// 男女共通アバター調査 & 整形
for($i = 0 ; $i < count($avaname1[1]) ; $i++){
	$breakflag = false;
	for($j = 0 ; $j < count($avaname2[1]) ; $j++){
		if($avasrc1[2][$i] == $avasrc2[2][$j]){
			if($countC !=0 && $countC % 6 == 0){
				$listnameC = $listnameC."\n";
				$listimgC = $listimgC."\n";
				$stackC = $stackC.$listimgC.$listnameC;
				$listnameC = $listimgC = "|";
			}
			$listnameC = $listnameC.$avaname1[1][$i]."|";
			$listimgC = $listimgC."&attachref(".$avasrc1[2][$i].",nolink);|";
			$countC++;
			// 女性アバターデータから共通アバターを消去
			array_splice($avaname2[1],$j,1);
			array_splice($avasrc2[2],$j,1);
			$breakflag = true;
			break;
		}
	}
	// 男性アバターデータ整形
	if(!$breakflag){
		if($countM !=0 && $countM % 6 == 0){
			$listnameM = $listnameM."\n";
			$listimgM = $listimgM."\n";
			$stackM = $stackM.$listimgM.$listnameM;
			$listnameM = $listimgM = "|";
		}
		$listnameM = $listnameM.$avaname1[1][$i]."|";
		$listimgM = $listimgM."&attachref(".$avasrc1[2][$i].",nolink);|";
		$countM++;
	}
}

// 女性アバターデータ整形
for($i = 0 ; $i < count($avaname2[1]) ; $i++){
	if($i !=0 && $i % 6 == 0){
		$listnameF = $listnameF."\n";
		$listimgF = $listimgF."\n";
		$stackF = $stackF.$listimgF.$listnameF;
		$listnameF = $listimgF = "|";
	}
	$listnameF = $listnameF.$avaname2[1][$i]."|";
	$listimgF = $listimgF."&attachref(".$avasrc2[2][$i].",nolink);|";
}

// リストの穴埋め(テーブル要素結合)
$stackC = $stackC.blankfill($countC, $listnameC, $listimgC);
$stackM = $stackM.blankfill($countM, $listnameM, $listimgM);
$stackF = $stackF.blankfill(count($avaname2[1]), $listnameF, $listimgF);

// ファイル書き出し
fwrite($fp, "*** 共通\n|");
for($i = 0 ; $i < $countC ; $i++){
	fwrite($fp, "130|");
	if($i == 5){
		break;
	}
}
fwrite($fp, "c\n".$stackC."\n*** 女性\n|");
for($i = 0 ; $i < count($avaname2[1]) ; $i++){
	fwrite($fp, "130|");
	if($i == 5){
		break;
	}
}
fwrite($fp, "c\n".$stackF."\n*** 男性\n|");
for($i = 0 ; $i < $countM ; $i++){
        fwrite($fp, "130|");
        if($i == 5){
                break;
        }
}
fwrite($fp, "c\n".$stackM."\n");

fwrite($fp, "** 特典\n|130|130|130|130|130|130|c\n|~無料|~3回購入|~5回購入|~7回購入|>|~10回購入|\n|&attachref(,nolink);|&attachref(,nolink);|&attachref(,nolink);|&attachref(,nolink);|&attachref(,nolink);|&attachref(,nolink);|\n|||||&color(red){名前};|&color(blue){名前};|\n");


/*-----   HTML取得&解析関数   -----*/
function HTMLget($url, $type)
{
	// Webデータ取得
	$html = file_get_contents($url);
	$html = mb_convert_encoding($html, "utf-8", "SJIS");
	//$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'ASCII, JIS, UTF-8, EUC-JP, SJIS');

	//echo $html;

	// HTMLからアバター名部分を取得
	if($type == 1){
		preg_match_all( '/<p class="itemCheck">(.*?)<\/p>/is', $html, $matches);
		// 表示
		/*for($i = 0 ; $i < count($matches[1]) ; $i++){
			echo $matches[1][$i]."\n";
		}*/
		return $matches;
	}

	if($type == 2){
		$html = str_replace(array("\r", "\n"), '', $html);
		preg_match_all('/Img\"><img.*?src=(["\'])(.+?)\1.*?>/is', $html, $matches);
		// 表示
		/*for($i = 0 ; $i < count($matches[2]) ; $i++){
			echo $matches[2][$i]."\n";
		}*/
		return $matches;
	}
}

/*----    穴埋め関数   ----*/
function blankfill($count, $name, $img){
	if($count > 6 && $count%6 != 0){
		for($i = 0 ; $i < 5-$count%6 ; $i++){
			$name = $name.">|";
			$img = $img.">|";
		}
		$name = $name."~|\n";
		$img = $img."|\n";
	}
	else{
		$name = $name."\n";
		$img = $img."\n";
	}
	return $img.$name;
}


fclose($fp);
?>
