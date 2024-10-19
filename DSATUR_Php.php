<?php
// Author: The Dude
// Date: 2002

$colorsommet="";
$DSAT="";
$s=0;
$color="";
$matrice="";
$table="";
$N=0;// nombre de sommets
$M=0;// nombre d'artes


function read_url($url)
{
	global $url, $table;
	
	if ($url==""){
		print "<p>Erreur url=$url <p>";
		exit;
		}
	print "<p>Dimacs graph url : <a href=$url>$url</a><p>";
	$fp = fopen("$url", "r");
	$content = "";
	while(!feof($fp)) $content .= fread($fp, "4096");
	fclose($fp);
	$table = explode("\n",$content);
}
 
 
function zero_matrice($taille)
{
	global $matrice;
	for ($i=0;$i<$taille;$i++)
		for ($j=0;$j<$taille;$j++)	
			$matrice[$i][$j]=0;$matrice[$j][$i]=0; 	
 }
 	
 	
function init_matrice()
{
	global $matrice, $table, $N, $M;
	$lg=sizeof($table);
	
	for($i=0;$i<$lg;$i++) { 
		$ligne = explode(" ",$table[$i]);
		switch ($ligne[0]):
			case "p":
				     $N=$ligne[2];$M=$ligne[3];
				     zero_matrice($N);
				     break;
	
			case "e":
					$i1=$ligne[1];$j1=$ligne[2];
				    $matrice[$i1][$j1]=1;$matrice[$j1][$i1]=1;
				    break;
				     
				     			     
					default:
				    break;
		endswitch;
		}
}// function
	  


/*-----------------------------------------------------------------------------------*/
/*-----------------------------------------------------------------------------------*/
/* initialisation du vecteur de la coloration des sommets*/
function init()
{	
	global $N,$colorsommet,$matrice,$DSAT;
	for ($i = 1; $i <= $N; $i++)  $colorsommet[$i]=-1;

	for ($i = 1; $i <= $N; $i++) {
		$somme=0;
		for ($j = 1; $j <= $N; $j++) $somme=$somme+$matrice[$i][$j];
		$DSAT[$i]=$somme;
		}
}// fin init



/*-----------------------------------------------------------------------------------*/
/*-----------------------------------------------------------------------------------*/
/*recherche du sommet de dsat max*/
function recherchesommet()
{
		global $N,$DSAT,$s;
		$eps=0;
		for ($j = 1; $j <= $N; $j++) {	
			if ($DSAT[$j] > $eps) {
				$eps=$DSAT[$j];
				$s=$j;
				}
			}
}
	


/*----------------------------------------------------------------------------------*/
/* fonction remplicolor :donne un vecteur indice par les couleurs avec 0 ou 1 dedans*/
/*----------------------------------------------------------------------------------*/
function remplicolor ($sommet_a_etudier)
{	
	global $N,$matrice,$colorsommet;
	
	for ($i = 1; $i <= $N; $i++) $used_color[$i]=0;

	for ($i = 1; $i <= $N; $i++)
		if ($matrice[$sommet_a_etudier][$i]==1)
			if ($colorsommet[$i] != -1) {
				$used_color[$colorsommet[$i]]=1;
				//print "<br>remplicolor: sommet s=$s voisin=$i couleur($i)=$colorsommet[$i] ";
				}
	return $used_color;
}



/*----------------------------------------------------------------------------------*/
/*		recherche de la couleur minimum pour la coloration du sommet				*/
/*----------------------------------------------------------------------------------*/
function recherchecouleurmin($vecteur_color)
{
	global $color,$colorsommet,$s;
	$i = 0;
	while ($vecteur_color[$i] != 0){
    	$i++;
    }
	$colorsommet[$s]=$i;
	//print "<br>recherchecouleurmin : sommet s=$s avec couleur $i ";
}



/*----------------------------------------------------------------------------------*/
/*						mise ˆ jour de DSAT											*/
/*----------------------------------------------------------------------------------*/
function majDSAT()
{
	global $matrice,$N,$DSAT,$colorsommet,$s;
	for ($j = 1; $j <= $N; $j++)
	{
		if ($matrice[$s][$j]==1)
		{
			if ($colorsommet[$j]==-1)
			{
				$used_color=remplicolor($j);
				$nbrecouleur=0;
				for ($k = 0; $k < $N; $k++)
				{
					$nbrecouleur=$nbrecouleur+$used_color[$k];
				}
				$DSAT[$j]=$nbrecouleur;
			}
		}
	}
	$DSAT[$s]=0;
}
/*-----------------------------------------------------------------------------------*/
/*			VERIF COLOR					   											 */
/*-----------------------------------------------------------------------------------*/
function verif_color()
{
	global $matrice, $colorsommet,  $N;
	print "<br>---------------<br> COLOR VERIFICATION:";
	for ($i=1;$i<=$N;$i++){
		for ($j=1;$j<=$N;$j++){
			if (($matrice[$i][$j]>0)&&($colorsommet[$j]==$colorsommet[$i])){
				print "<br> !!! Color conflit... ($i)c=$colorsommet[$i]----($j)c=$colorsommet[$j]<br>";
				$bad=3;
				}
			}
		}
	
	if ($bad==3)
		print "<br>===> Wrong coloration !!!!<br>";
	else
		print " OK.<br>";
}

/*-----------------------------------------------------------------------------------*/
/*			DISPLAY					   												 */
/*-----------------------------------------------------------------------------------*/
function display_color_des_sommets()
{
	global $colorsommet,  $N;
	print "<br>---------------<br> DISPLAY COLOR : <br>";
	for ($i=1;$i<=$N;$i++){
		print "\n<br> Vertex $i : color=$colorsommet[$i] \n";
		}
}


/*-----------------------------------------------------------------------------------*/
/*-----------------------------------------------------------------------------------*/
function display_matrice(){
	global $matrice,  $N, $M;
	print "<br>---------------<br>MATRICE N=$N M=$M<br>";
	for ($i=1;$i<=$N;$i++){
		print "\n<br> row $i: \n";
		for ($j=1;$j <= $N; $j++)	{
			$val = $matrice[$i][$j];
			print " $val ";
			}
		}
	}

//--------------------------------------------------//
//--------------------------------------------------//
// 						MAIN						//
//--------------------------------------------------//
//--------------------------------------------------//	

print "<html><head><title>DSAT Coloring Dimacs graph with an URL</title></head><body bgcolor=#FFFFFF text=#000000><font face=Arial,Helvetica,sans-serif>";
read_url($url);
init_matrice();	
init();
for ($j = 1; $j <= $N ; $j++)
{
	recherchesommet();
	if ($display_stage=="ok") print "<br> Stage $j: vertex selection $s(dsat=$DSAT[$s]) ";
	$used_color=remplicolor($s);
	recherchecouleurmin($used_color);
	if ($display_stage=="ok") print " color=$colorsommet[$s] ";
	majDSAT();
}
if ($matrix=="ok") display_matrice();


display_color_des_sommets();
if ($verif=="ok") verif_color();
// fin du programme principal
print"</font></body></html>";

	
?>
