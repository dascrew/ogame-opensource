
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html> 
<!-- Created on: 14.12.2004 --> 
<head> 
<style type="text/css"> 
 
 table { border:groove white 2px;  }
</style> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
<title>Скины ОГейм</title> 
<meta name="author" content="Ringkeeper"> 
<meta name="generator" content="2 Hï¿½nde, eine Tastatur, einen Kopf, nen Rechner, 20 Kippen, 3 Liter Kaffee,nen lieber Hend, ne sï¿½ï¿½e Icecoldbaby"> 
</head> 
</head> 
<body text="#FFFFFF" bgcolor=" #001746" link="#FFFFFF" alink="#FF0000" vlink="#FF0000" style="background-image:url(pic/background.gif);font-family:verdana;font-size:10px;"> 
<br><font face="verdana" size="18" color="#FFFFFF"> <div align="center"> <h1>Скины для OGame</h1></font></div><br> 
<br><font face="verdana" size="02" color="#FFFFFF"> <div align="center"> Большое спасибо NEFFE за программу предпросмотра скинов.</font></div><br> 
<br><font face="verdana" size="02" color="#FFFFFF"> <div align="center"> Список всех скинов можно найти в Настройках игры. Для этого нужно удалить путь к скину, тогда появится выпадающий список.</font></div><br> 
<br><font face="verdana" size="02" color="#FFFFFF"> <div align="center"> Авторские права на скины принадлежат их создателям.</font></div><br> 
 <table border="0" align="center" summary=""width=800px> 
<?php

$skins = [
    // shortname, longname, author, email

     [ 'reloaded', 'Reloaded', 'g3ck0', 'g3ck0@cnp-online.de' ] ,
     [ 'allycpb', 'Ally-CPB', 'Poll@', 'bla@blubb.de' ] ,
     [ 'asgard', 'Asgard', 'Der Lapper', 'bla@blubb.de' ] ,
     [ 'aurora', 'Aurora', 'Diamond', 'bla@blubb.de' ] ,
     [ 'vampir', 'Vampir', 'Meistervampir', 'Meistervampir@ogame-team.de' ] ,
     [ 'allesnurgeklaut', 'Allesnurgeklaut', 'GaLAxY', 'bla@blubb.de' ] ,
     [ 'bluedream', 'Bluedream', 'eSpGhost', 'bla@blubb.de' ] ,
     [ 'bluegalaxy', 'Bluegalaxy', 'BigMuffl', 'bla@blubb.de' ] ,
     [ 'blue-mx', 'Blue-MX', 'Steryc', 'bla@blubb.de' ] ,
     [ 'brotstyle', 'Brotstyle', 'BrotUser', 'bla@blubb.de' ] ,
     [ 'dd', 'DD', 'DarkDragon', 'bla@blubb.de' ] ,
     [ 'eclipse', 'Eclipse', 'Dracon', 'bla@blubb.de' ] ,
     [ 'empire', 'Empire', 'Medhiv', 'bla@blubb.de' ] ,
     [ 'g3cko', 'G3ck0', 'g3ck0', 'g3ck0@cnp-online.de' ] ,
     [ 'gruen', 'Gruen', 'eSpGhost', 'bla@blubb.de' ] ,
     [ 'infraos', 'Infraos', 'oldi', 'bla@blubb.de' ] ,
     [ 'lambda', 'Lambda', 'Eseno', 'bla@blubb.de' ] ,
     [ 'lego', 'Lego', 'Nolte', 'bla@blubb.de' ] ,
     [ 'militaryskin', 'Military', 'Warhorse', 'bla@blubb.de' ] ,
     [ 'okno', 'Okno', 'oknoeno', 'bla@blubb.de' ] ,
     [ 'ovisiofarbig', 'Ovisiofarbig', 'TheMaze/Spyme', 'bla@blubb.de' ] ,
     [ 'ovisio', 'Ovisio', 'Spyme', 'bla@blubb.de' ] ,
     [ 'paint', 'Paint', 'Daggoth', 'bla@blubb.de' ] ,
     [ 'redfuturistisch', 'Redfuturistisch', '.:Diamond:.', 'bla@blubb.de' ] ,
     [ 'redvision', 'Redvision', 'SyRuS', 'bla@blubb.de' ] ,
     [ 'shadowpato', 'Shadowpato', 'ShadowPato', 'bla@blubb.de' ] ,
     [ 'simpel', 'Simpel', 'janKG', 'bla@blubb.de' ] ,
     [ 'starwars', 'Starwars', 'Conan', 'bla@blubb.de' ] ,
     [ 'w4wooden4ce', 'W4wooden4ce', '[W4]hoLogramm', 'bla@blubb.de' ] ,
     [ 'xonic', 'Xonic', 'xonic', 'bla@blubb.de' ] ,
     [ 'skin1', '1 Skin', 'Piratentunte', 'bla@blubb.de' ] ,
     [ 'brace', 'Brace', 'BraCe', 'bla@blubb.de' ] ,
     [ 'bluechaos', 'Bluechaos', '002', 'bla@blubb.de' ] ,
     [ 'epicblue', 'Epicblue', '', 'bla@blubb.de' ] ,
     [ 'quadratorstyle', 'Quadrator Style', 'Quadrator', 'Quadrator@gmx.net' ] ,
     [ 'real', 'Real', 'Thanos', 'tobi@tobiweb.de' ] ,
     [ 'blueplanet', 'BluePlanet', 'Mic2003', 'mic2003-skin@lycos.de' ] ,
     [ '', '', '', 'bla@blubb.de' ] ,

];

$index = 0;

foreach ($skins as $i => $skin) {
    if ($index == 0) {
        echo "	<tr> \n";
    }
    ?>
		<td > 
			<table border="0" align="center" summary="" width=100%> 
				<tr> 
					<td align="center"><a href="index2.php?i=<?=$skin[0];?>" target="_blank"><img src="pic/<?=$skin[0];?>.jpg" width="100" height="100" alt="" align="middle"></a></td> 
				</tr> 
				<tr> 
					<td align="center"><a href="zip/<?=$skin[0];?>.zip">Скачать</a></td> 
				</tr> 
				<tr> 
					<td align="center">Скин <?=$skin[1];?>. Автор: <a href="mailto:<?=$skin[3];?>"><?=$skin[2];?></a></td> 
				</tr> 
			</table> 
		</td> 
<?php

        if ($index == 1) {
            echo "    </tr> \n";
        }
    $index ^= 1;
}

?>

</table> 
</body> 
</html>