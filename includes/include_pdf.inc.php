<?php
	
	require_once(INCLUDE_DIR."fpdf_183".DIRECTORY_SEPARATOR."fpdf.php");

	class PDF_WriteTag extends FPDF
	{
		var $wLine; // Maximum width of the line
		var $hLine; // Height of the line
		var $Text; // Text to display
		var $border;
		var $align; // Justification of the text
		var $fill;
		var $Padding;
		var $lPadding;
		var $tPadding;
		var $bPadding;
		var $rPadding;
		var $TagStyle; // Style for each tag
		var $Indent;
		var $Space; // Minimum space between words
		var $PileStyle; 
		var $Line2Print; // Line to display
		var $NextLineBegin; // Buffer between lines 
		var $TagName;
		var $Delta; // Maximum width minus width
		var $StringLength; 
		var $LineLength;
		var $wTextLine; // Width minus paddings
		var $nbSpace; // Number of spaces in the line
		var $Xini; // Initial position
		var $href; // Current URL
		var $TagHref; // URL for a cell
	
		// Public Functions
	
		function WriteTag($w, $h, $txt, $border=0, $align="J", $fill=false, $padding=0)
		{
			$this->wLine=$w;
			$this->hLine=$h;
			$this->Text=trim($txt);
			$this->Text=preg_replace("/\n|\r|\t/","",$this->Text);
			$this->border=$border;
			$this->align=$align;
			$this->fill=$fill;
			$this->Padding=$padding;
	
			$this->Xini=$this->GetX();
			$this->href="";
			$this->PileStyle=array();		
			$this->TagHref=array();
			$this->LastLine=false;
	
			$this->SetSpace();
			$this->Padding();
			$this->LineLength();
			$this->BorderTop();
	
			while($this->Text!="")
			{
				$this->MakeLine();
				$this->PrintLine();
			}
	
			$this->BorderBottom();
		}
	
	
		function SetStyle($tag, $family, $style, $size, $color, $indent=-1)
		{
			 $tag=trim($tag);
			 $this->TagStyle[$tag]['family']=trim($family);
			 $this->TagStyle[$tag]['style']=trim($style);
			 $this->TagStyle[$tag]['size']=trim($size);
			 $this->TagStyle[$tag]['color']=trim($color);
			 $this->TagStyle[$tag]['indent']=$indent;
		}
	
	
		// Private Functions
	
		function SetSpace() // Minimal space between words
		{
			$tag=$this->Parser($this->Text);
			$this->FindStyle($tag[2],0);
			$this->DoStyle(0);
			$this->Space=$this->GetStringWidth(" ");
		}
	
	
		function Padding()
		{
			if(preg_match("/^.+,/",$this->Padding)) {
				$tab=explode(",",$this->Padding);
				$this->lPadding=$tab[0];
				$this->tPadding=$tab[1];
				if(isset($tab[2]))
					$this->bPadding=$tab[2];
				else
					$this->bPadding=$this->tPadding;
				if(isset($tab[3]))
					$this->rPadding=$tab[3];
				else
					$this->rPadding=$this->lPadding;
			}
			else
			{
				$this->lPadding=$this->Padding;
				$this->tPadding=$this->Padding;
				$this->bPadding=$this->Padding;
				$this->rPadding=$this->Padding;
			}
			if($this->tPadding<$this->LineWidth)
				$this->tPadding=$this->LineWidth;
		}
	
	
		function LineLength()
		{
			if($this->wLine==0)
				$this->wLine=$this->w - $this->Xini - $this->rMargin;
	
			$this->wTextLine = $this->wLine - $this->lPadding - $this->rPadding;
		}
	
	
		function BorderTop()
		{
			$border=0;
			if($this->border==1)
				$border="TLR";
			$this->Cell($this->wLine,$this->tPadding,"",$border,0,'C',$this->fill);
			$y=$this->GetY()+$this->tPadding;
			$this->SetXY($this->Xini,$y);
		}
	
	
		function BorderBottom()
		{
			$border=0;
			if($this->border==1)
				$border="BLR";
			$this->Cell($this->wLine,$this->bPadding,"",$border,0,'C',$this->fill);
		}
	
	
		function DoStyle($tag) // Applies a style
		{
			$tag=trim($tag);
			$this->SetFont($this->TagStyle[$tag]['family'],
				$this->TagStyle[$tag]['style'],
				$this->TagStyle[$tag]['size']);
	
			$tab=explode(",",$this->TagStyle[$tag]['color']);
			if(count($tab)==1)
				$this->SetTextColor($tab[0]);
			else
				$this->SetTextColor($tab[0],$tab[1],$tab[2]);
		}
	
	
		function FindStyle($tag, $ind) // Inheritance from parent elements
		{
			$tag=trim($tag);
	
			// Family
			if($this->TagStyle[$tag]['family']!="")
				$family=$this->TagStyle[$tag]['family'];
			else
			{
				reset($this->PileStyle);
				while(list($k,$val)=each($this->PileStyle))
				{
					$val=trim($val);
					if($this->TagStyle[$val]['family']!="") {
						$family=$this->TagStyle[$val]['family'];
						break;
					}
				}
			}
	
			// Style
			$style="";
			$style1=strtoupper($this->TagStyle[$tag]['style']);
			if($style1!="N")
			{
				$bold=false;
				$italic=false;
				$underline=false;
				reset($this->PileStyle);
				while(list($k,$val)=each($this->PileStyle))
				{
					$val=trim($val);
					$style1=strtoupper($this->TagStyle[$val]['style']);
					if($style1=="N")
						break;
					else
					{
						if(strpos($style1,"B")!==false)
							$bold=true;
						if(strpos($style1,"I")!==false)
							$italic=true;
						if(strpos($style1,"U")!==false)
							$underline=true;
					} 
				}
				if($bold)
					$style.="B";
				if($italic)
					$style.="I";
				if($underline)
					$style.="U";
			}
	
			// Size
			if($this->TagStyle[$tag]['size']!=0)
				$size=$this->TagStyle[$tag]['size'];
			else
			{
				reset($this->PileStyle);
				while(list($k,$val)=each($this->PileStyle))
				{
					$val=trim($val);
					if($this->TagStyle[$val]['size']!=0) {
						$size=$this->TagStyle[$val]['size'];
						break;
					}
				}
			}
	
			// Color
			if($this->TagStyle[$tag]['color']!="")
				$color=$this->TagStyle[$tag]['color'];
			else
			{
				reset($this->PileStyle);
				while(list($k,$val)=each($this->PileStyle))
				{
					$val=trim($val);
					if($this->TagStyle[$val]['color']!="") {
						$color=$this->TagStyle[$val]['color'];
						break;
					}
				}
			}
			 
			// Result
			$this->TagStyle[$ind]['family']=$family;
			$this->TagStyle[$ind]['style']=$style;
			$this->TagStyle[$ind]['size']=$size;
			$this->TagStyle[$ind]['color']=$color;
			$this->TagStyle[$ind]['indent']=$this->TagStyle[$tag]['indent'];
		}
	
	
		function Parser($text)
		{
			$tab=array();
			// Closing tag
			if(preg_match("|^(</([^>]+)>)|",$text,$regs)) {
				$tab[1]="c";
				$tab[2]=trim($regs[2]);
			}
			// Opening tag
			else if(preg_match("|^(<([^>]+)>)|",$text,$regs)) {
				$regs[2]=preg_replace("/^a/","a ",$regs[2]);
				$tab[1]="o";
				$tab[2]=trim($regs[2]);
	
				// Presence of attributes
				if(preg_match("/(.+) (.+)='(.+)'/",$regs[2])) {
					$tab1=preg_split("/ +/",$regs[2]);
					$tab[2]=trim($tab1[0]);
					while(list($i,$couple)=each($tab1))
					{
						if($i>0) {
							$tab2=explode("=",$couple);
							$tab2[0]=trim($tab2[0]);
							$tab2[1]=trim($tab2[1]);
							$end=strlen($tab2[1])-2;
							$tab[$tab2[0]]=substr($tab2[1],1,$end);
						}
					}
				}
			}
			 // Space
			 else if(preg_match("/^( )/",$text,$regs)) {
				$tab[1]="s";
				$tab[2]=' ';
			}
			// Text
			else if(preg_match("/^([^< ]+)/",$text,$regs)) {
				$tab[1]="t";
				$tab[2]=trim($regs[1]);
			}
	
			$begin=strlen($regs[1]);
			 $end=strlen($text);
			 $text=substr($text, $begin, $end);
			$tab[0]=$text;
	
			return $tab;
		}
	
	
		function MakeLine()
		{
			$this->Text.=" ";
			$this->LineLength=array();
			$this->TagHref=array();
			$Length=0;
			$this->nbSpace=0;
	
			$i=$this->BeginLine();
			$this->TagName=array();
	
			if($i==0) {
				$Length=$this->StringLength[0];
				$this->TagName[0]=1;
				$this->TagHref[0]=$this->href;
			}
	
			while($Length<$this->wTextLine)
			{
				$tab=$this->Parser($this->Text);
				$this->Text=$tab[0];
				if($this->Text=="") {
					$this->LastLine=true;
					break;
				}
	
				if($tab[1]=="o") {
					array_unshift($this->PileStyle,$tab[2]);
					$this->FindStyle($this->PileStyle[0],$i+1);
	
					$this->DoStyle($i+1);
					$this->TagName[$i+1]=1;
					if($this->TagStyle[$tab[2]]['indent']!=-1) {
						$Length+=$this->TagStyle[$tab[2]]['indent'];
						$this->Indent=$this->TagStyle[$tab[2]]['indent'];
					}
					if($tab[2]=="a")
						$this->href=$tab['href'];
				}
	
				if($tab[1]=="c") {
					array_shift($this->PileStyle);
					if(isset($this->PileStyle[0]))
					{
						$this->FindStyle($this->PileStyle[0],$i+1);
						$this->DoStyle($i+1);
					}
					$this->TagName[$i+1]=1;
					if($this->TagStyle[$tab[2]]['indent']!=-1) {
						$this->LastLine=true;
						$this->Text=trim($this->Text);
						break;
					}
					if($tab[2]=="a")
						$this->href="";
				}
	
				if($tab[1]=="s") {
					$i++;
					$Length+=$this->Space;
					$this->Line2Print[$i]="";
					if($this->href!="")
						$this->TagHref[$i]=$this->href;
				}
	
				if($tab[1]=="t") {
					$i++;
					$this->StringLength[$i]=$this->GetStringWidth($tab[2]);
					$Length+=$this->StringLength[$i];
					$this->LineLength[$i]=$Length;
					$this->Line2Print[$i]=$tab[2];
					if($this->href!="")
						$this->TagHref[$i]=$this->href;
				 }
	
			}
	
			trim($this->Text);
			if($Length>$this->wTextLine || $this->LastLine==true)
				$this->EndLine();
		}
	
	
		function BeginLine()
		{
			$this->Line2Print=array();
			$this->StringLength=array();
	
			if(isset($this->PileStyle[0]))
			{
				$this->FindStyle($this->PileStyle[0],0);
				$this->DoStyle(0);
			}
	
			if(count($this->NextLineBegin)>0) {
				$this->Line2Print[0]=$this->NextLineBegin['text'];
				$this->StringLength[0]=$this->NextLineBegin['length'];
				$this->NextLineBegin=array();
				$i=0;
			}
			else {
				preg_match("/^(( *(<([^>]+)>)* *)*)(.*)/",$this->Text,$regs);
				$regs[1]=str_replace(" ", "", $regs[1]);
				$this->Text=$regs[1].$regs[5];
				$i=-1;
			}
	
			return $i;
		}
	
	
		function EndLine()
		{
			if(end($this->Line2Print)!="" && $this->LastLine==false) {
				$this->NextLineBegin['text']=array_pop($this->Line2Print);
				$this->NextLineBegin['length']=end($this->StringLength);
				array_pop($this->LineLength);
			}
	
			while(end($this->Line2Print)==="")
				array_pop($this->Line2Print);
	
			$this->Delta=$this->wTextLine-end($this->LineLength);
	
			$this->nbSpace=0;
			for($i=0; $i<count($this->Line2Print); $i++) {
				if($this->Line2Print[$i]=="")
					$this->nbSpace++;
			}
		}
	
	
		function PrintLine()
		{
			$border=0;
			if($this->border==1)
				$border="LR";
			$this->Cell($this->wLine,$this->hLine,"",$border,0,'C',$this->fill);
			$y=$this->GetY();
			$this->SetXY($this->Xini+$this->lPadding,$y);
	
			if($this->Indent!=-1) {
				if($this->Indent!=0)
					$this->Cell($this->Indent,$this->hLine);
				$this->Indent=-1;
			}
	
			$space=$this->LineAlign();
			$this->DoStyle(0);
			for($i=0; $i<count($this->Line2Print); $i++)
			{
				if(isset($this->TagName[$i]))
					$this->DoStyle($i);
				if(isset($this->TagHref[$i]))
					$href=$this->TagHref[$i];
				else
					$href='';
				if($this->Line2Print[$i]=="")
					$this->Cell($space,$this->hLine,"         ",0,0,'C',false,$href);
				else
					$this->Cell($this->StringLength[$i],$this->hLine,$this->Line2Print[$i],0,0,'C',false,$href);
			}
	
			$this->LineBreak();
			if($this->LastLine && $this->Text!="")
				$this->EndParagraph();
			$this->LastLine=false;
		}
	
	
		function LineAlign()
		{
			$space=$this->Space;
			if($this->align=="J") {
				if($this->nbSpace!=0)
					$space=$this->Space + ($this->Delta/$this->nbSpace);
				if($this->LastLine)
					$space=$this->Space;
			}
	
			if($this->align=="R")
				$this->Cell($this->Delta,$this->hLine);
	
			if($this->align=="C")
				$this->Cell($this->Delta/2,$this->hLine);
	
			return $space;
		}
	
	
		function LineBreak()
		{
			$x=$this->Xini;
			$y=$this->GetY()+$this->hLine;
			$this->SetXY($x,$y);
		}
	
	
		function EndParagraph()
		{
			$border=0;
			if($this->border==1)
				$border="LR";
			$this->Cell($this->wLine,$this->hLine/2,"",$border,0,'C',$this->fill);
			$x=$this->Xini;
			$y=$this->GetY()+$this->hLine/2;
			$this->SetXY($x,$y);
		}
	
	} // End of class
	
	class PDFEXTRA extends PDF_WriteTag
	{
	
		var $DisplayPreferences=''; //EDITEI - added
		var $outlines=array(); //EDITEI - added
		var $OutlineRoot; //EDITEI - added
		var $flowingBlockAttr; //EDITEI - added
		var $departamento;
		var $cliente;
		var $unidade;
		var $subsistema;
		var $area;
		var $logotipocliente;
		var $logotipodvm;
		var $numeros_interno;
		var $numero_cliente;
		var $titulo;
		var $titulo2;
		var $setor;
		var $codigodoc;
		var $codigo;
		var $emissao;
		var $versao_documento;
		var $setorextenso;
		var $solicitante;
		var $visitante;
		var $numeroregs;

		/*******************************************************************************
		*                                                                              *
		*                               Public methods                                 *
		*                                                                              *
		*******************************************************************************/

		function departamento()
		{
			return $this->departamento;
		}

		function Cliente()
		{
			return $this->cliente;
		}

		function unidade()
		{
			return $this->unidade;
		}

		function Subsistema()
		{
			return $this->subsistema;
		}

		function Area()
		{
			return $this->area;
		}

		function Logotipocliente()
		{
			return $this->logotipocliente;
		}

		function Logotipodvm()
		{
			return $this->logotipodvm;
		}

		function Numdvm()
		{
			return $this->numeros_interno;
		}

		function Numcliente()
		{
			return $this->numero_cliente;
		}

		function Numeroregs()
		{
			return $this->numeroregs;
		}

		function Visitante()
		{
			return $this->visitante;
		}

		function Titulo()
		{
			return $this->titulo;
		}

		function Titulo2()
		{
			return $this->titulo2;
		}

		function setor()
		{
			return $this->setor;
		}

		function codigodoc()
		{
			return $this->codigodoc;
		}

		function codigo()
		{
			return $this->codigo;
		}

		function Emissao()
		{
			return $this->emissao;
		}

		function Revisao()
		{
			return $this->versao_documento;
		}

		function setorextenso()
		{
			return $this->setorextenso;
		}

		function Solicitante()
		{
			return $this->solicitante;
		}

		function TamForm()
		{
			return $this->TamForm;
		}

		function i25($xpos, $ypos, $code, $basewidth=1, $height=10)
		{

			$wide = $basewidth;
			$narrow = $basewidth / 3 ;

			// wide/narrow codes for the digits
			$barChar['0'] = 'nnwwn';
			$barChar['1'] = 'wnnnw';
			$barChar['2'] = 'nwnnw';
			$barChar['3'] = 'wwnnn';
			$barChar['4'] = 'nnwnw';
			$barChar['5'] = 'wnwnn';
			$barChar['6'] = 'nwwnn';
			$barChar['7'] = 'nnnww';
			$barChar['8'] = 'wnnwn';
			$barChar['9'] = 'nwnwn';
			$barChar['A'] = 'nn';
			$barChar['Z'] = 'wn';

			// add leading zero if code-length is odd
			if(strlen($code) % 2 != 0){
				$code = '0' . $code;
			}

			$this->SetFont('Arial','',10);
			$this->Text($xpos, $ypos + $height + 4, $code);
			$this->SetFillColor(0);

			// add start and stop codes
			$code = 'AA'.strtolower($code).'ZA';

			for($i=0; $i<strlen($code); $i=$i+2){
				// choose next pair of digits
				$charBar = $code{$i};
				$charSpace = $code{$i+1};
				// check whether it is a valid digit
				if(!isset($barChar[$charBar])){
					$this->Error('Invalid character in barcode: '.$charBar);
				}
				if(!isset($barChar[$charSpace])){
					$this->Error('Invalid character in barcode: '.$charSpace);
				}
				// create a wide/narrow-sequence (first digit=bars, second digit=spaces)
				$seq = '';
				for($s=0; $s<strlen($barChar[$charBar]); $s++){
					$seq .= $barChar[$charBar]{$s} . $barChar[$charSpace]{$s};
				}
				for($bar=0; $bar<strlen($seq); $bar++){
					// set lineWidth depending on value
					if($seq{$bar} == 'n'){
						$lineWidth = $narrow;
					}else{
						$lineWidth = $wide;
					}
					// draw every second value, because the second digit of the pair is represented by the spaces
					if($bar % 2 == 0){
						$this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
					}
					$xpos += $lineWidth;
				}
			}
		}

		//Thanks to Ron Korving for the WordWrap() function
		function WordWrap(&$text, $maxwidth)
		{
			$biggestword=0;//EDITEI
			$toonarrow=false;//EDITEI

			$text = trim($text);
			if ($text==='') return 0;
			$space = $this->GetStringWidth(' ');
			$lines = explode("\n", $text);
			$text = '';
			$count = 0;

			foreach ($lines as $line)
			{
				$words = preg_split('/ +/', $line);
				$width = 0;

				foreach ($words as $word)
				{
					$wordwidth = $this->GetStringWidth($word);

					//EDITEI
					//Warn user that maxwidth is insufficient
					if ($wordwidth > $maxwidth)
					{
						if ($wordwidth > $biggestword) $biggestword = $wordwidth;
						$toonarrow=true;//EDITEI
					}
					if ($width + $wordwidth <= $maxwidth)
					{
						$width += $wordwidth + $space;
						$text .= $word.' ';
					}
					else
					{
						$width = $wordwidth + $space;
						$text = rtrim($text)."\n".$word.' ';
						$count++;
					}
				}
				$text = rtrim($text)."\n";
				$count++;
			}
			$text = rtrim($text);

			//Return -(wordsize) if word is bigger than maxwidth 
			if ($toonarrow) return -$biggestword;
			else return $count;
		}

		//EDITEI
		//Thanks to Seb(captainseb@wanadoo.fr) for the _SetTextRendering() and SetTextOutline() functions
		/** 
		* Set Text Rendering Mode 
		* @param int $mode Set the rendering mode.<ul><li>0 : Fill text (default)</li><li>1 : Stroke</li><li>2 : Fill & stroke</li></ul> 
		* @see SetTextOutline() 
		*/ 
		//This function is not being currently used
		function _SetTextRendering($mode) 
		{ 
			if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) 
				$this->Error("Text rendering mode should be 0, 1 or 2 (value : $mode)"); 
				$this->_out($mode.' Tr'); 
		} 

		/** 
		* Set Text Ouline On/Off 
		* @param mixed $width If set to false the text rending mode is set to fill, else it's the width of the outline 
		* @param int $r If g et b are given, red component; if not, indicates the gray level. Value between 0 and 255 
		* @param int $g Green component (between 0 and 255) 
		* @param int $b Blue component (between 0 and 255) 
		* @see _SetTextRendering() 
		*/ 
		function SetTextOutline($width, $r=0, $g=-1, $b=-1) //EDITEI
		{ 
			if ($width == false) //Now resets all values
			{ 
				$this->outline_on = false;
				$this->SetLineWidth(0.2); 
				$this->SetDrawColor(0); 
				$this->_setTextRendering(0); 
				$this->_out('0 Tr'); 
			}
			else
			{ 
				$this->SetLineWidth($width); 
				$this->SetDrawColor($r, $g , $b); 
				$this->_out('2 Tr'); //Fixed
			} 
		}

		//function Circle() thanks to Olivier PLATHEY
		//EDITEI
		function Circle($x,$y,$r,$style='')
		{
			$this->Ellipse($x,$y,$r,$r,$style);
		}

		//function Ellipse() thanks to Olivier PLATHEY
		//EDITEI
		function Ellipse($x,$y,$rx,$ry,$style='D')
		{
			if($style=='F') $op='f';
			elseif($style=='FD' or $style=='DF') $op='B';
			else $op='S';
			$lx=4/3*(M_SQRT2-1)*$rx;
			$ly=4/3*(M_SQRT2-1)*$ry;
			$k=$this->k;
			$h=$this->h;
			$this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
				($x+$rx)*$k,($h-$y)*$k,
				($x+$rx)*$k,($h-($y-$ly))*$k,
				($x+$lx)*$k,($h-($y-$ry))*$k,
				$x*$k,($h-($y-$ry))*$k));
			$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
				($x-$lx)*$k,($h-($y-$ry))*$k,
				($x-$rx)*$k,($h-($y-$ly))*$k,
				($x-$rx)*$k,($h-$y)*$k));
			$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
				($x-$rx)*$k,($h-($y+$ly))*$k,
				($x-$lx)*$k,($h-($y+$ry))*$k,
				$x*$k,($h-($y+$ry))*$k));
			$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
				($x+$lx)*$k,($h-($y+$ry))*$k,
				($x+$rx)*$k,($h-($y+$ly))*$k,
				($x+$rx)*$k,($h-$y)*$k,
				$op));
		}

		//EDITEI - Done after reading a little about PDF reference guide
		function DottedRect($x=100,$y=150,$w=50,$h=50)
		{
			$x *= $this->k ;
			$y = ($this->h-$y)*$this->k;
			$w *= $this->k ;
			$h *= $this->k ;// - h?
			
			$herex = $x;
			$herey = $y;

			//Make fillcolor == drawcolor
			$bak_fill = $this->FillColor;
			$this->FillColor = $this->DrawColor;
			$this->FillColor = str_replace('RG','rg',$this->FillColor);
			$this->_out($this->FillColor);
			
			while ($herex < ($x + $w)) //draw from upper left to upper right
			{
				$this->DrawDot($herex,$herey);
				$herex += (3*$this->k);
			}
			$herex = $x + $w;
			while ($herey > ($y - $h)) //draw from upper right to lower right
			{
				$this->DrawDot($herex,$herey);
				$herey -= (3*$this->k);
			}
			$herey = $y - $h;
			while ($herex > $x) //draw from lower right to lower left
			{
				$this->DrawDot($herex,$herey);
				$herex -= (3*$this->k);
			}
			$herex = $x;
			while ($herey < $y) //draw from lower left to upper left
			{
				$this->DrawDot($herex,$herey);
				$herey += (3*$this->k);
			}
			$herey = $y;

			$this->FillColor = $bak_fill;
			$this->_out($this->FillColor); //return fillcolor back to normal
		}

		//EDITEI - Done after reading a little about PDF reference guide
		function DrawDot($x,$y) //center x y
		{
			$op = 'B'; // draw Filled Dots
			//F == fill //S == stroke //B == stroke and fill 
			$r = 0.5 * $this->k;  //raio
			
			//Start Point
			$x1 = $x - $r;
			$y1 = $y;
			//End Point
			$x2 = $x + $r;
			$y2 = $y;
			//Auxiliar Point
			$x3 = $x;
			$y3 = $y + (2*$r);// 2*raio to make a round (not oval) shape  

			//Round join and cap
			$s="\n".'1 J'."\n";
			$s.='1 j'."\n";

			//Upper circle
			$s.=sprintf('%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
			$s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);//Bezier curve
			//Lower circle
			$y3 = $y - (2*$r);
			$s.=sprintf("\n".'%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
			$s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);
			$s.=$op."\n"; //stroke and fill

			//Draw in PDF file
			$this->_out($s);
		}

		function DisplayPreferences($preferences)
		{
			$this->DisplayPreferences .= $preferences;
		}

		// AQUI !!!!!!!
		function VCell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0)
		{
			//Output a cell
			$k=$this->k;
			if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
			{
				$x=$this->x;
				$ws=$this->ws;
				if($ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->AddPage($this->CurOrientation);
				$this->x=$x;
				if($ws>0)
				{
					$this->ws=$ws;
					$this->_out(sprintf('%.3f Tw', $ws*$k));
				}
			}
			if($w==0)
				$w=$this->w-$this->rMargin-$this->x;
			$s='';
			// begin change Cell function 
			if($fill==1 or $border>0)
			{
				if($fill==1)
					$op=($border>0) ? 'B' : 'f';
				else
					$op='S';
				if ($border>1) {
					$s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ', $border, 
								$this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
				}
				else
					$s=sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
			}
			if(is_string($border))
			{
				$x=$this->x;
				$y=$this->y;
				if(is_int(strpos($border, 'L')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'l')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
					
				if(is_int(strpos($border, 'T')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
				else if(is_int(strpos($border, 't')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
				
				if(is_int(strpos($border, 'R')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'r')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				
				if(is_int(strpos($border, 'B')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'b')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
			}
			if(trim($txt)!='')
			{
				$cr=substr_count($txt, "\n");
				if ($cr>0) { // Multi line
					$txts = explode("\n", $txt);
					$lines = count($txts);
					for($l=0;$l<$lines;$l++) {
						$txt=$txts[$l];
						$w_txt=$this->GetStringWidth($txt);
						if ($align=='U')
							$dy=$this->cMargin+$w_txt;
						elseif($align=='D')
							$dy=$h-$this->cMargin;
						else
							$dy=($h+$w_txt)/2;
						$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
						if($this->ColorFlag)
							$s.='q '.$this->TextColor.' ';
						$s.=sprintf('BT 0 1 -1 0 %.2f %.2f Tm (%s) Tj ET ', 
							($this->x+.5*$w+(.7+$l-$lines/2)*$this->FontSize)*$k, 
							($this->h-($this->y+$dy))*$k, $txt);
						if($this->ColorFlag)
							$s.='Q ';
					}
				}
				else { // Single line
					$w_txt=$this->GetStringWidth($txt);
					$Tz=100;
					if ($w_txt>$h-2*$this->cMargin) {
						$Tz=($h-2*$this->cMargin)/$w_txt*100;
						$w_txt=$h-2*$this->cMargin;
					}
					if ($align=='U')
						$dy=$this->cMargin+$w_txt;
					elseif($align=='D')
						$dy=$h-$this->cMargin;
					else
						$dy=($h+$w_txt)/2;
					$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
					if($this->ColorFlag)
						$s.='q '.$this->TextColor.' ';
					$s.=sprintf('q BT 0 1 -1 0 %.2f %.2f Tm %.2f Tz (%s) Tj ET Q ', 
								($this->x+.5*$w+.3*$this->FontSize)*$k, 
								($this->h-($this->y+$dy))*$k, $Tz, $txt);
					if($this->ColorFlag)
						$s.='Q ';
				}
			}
			// end change Cell function 
			if($s)
				$this->_out($s);
			$this->lasth=$h;
			if($ln>0)
			{
				//Go to next line
				$this->y+=$h;
				if($ln==1)
					$this->x=$this->lMargin;
			}
			else
				$this->x+=$w;
		}

		function TextWithDirection($x, $y, $txt, $direction='R')
		{
			$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
			if ($direction=='R')
				$s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET', 1, 0, 0, 1, $x*$this->k, ($this->h-$y)*$this->k, $txt);
			elseif ($direction=='L')
				$s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET', -1, 0, 0, -1, $x*$this->k, ($this->h-$y)*$this->k, $txt);
			elseif ($direction=='U')
				$s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET', 0, 1, -1, 0, $x*$this->k, ($this->h-$y)*$this->k, $txt);
			elseif ($direction=='D')
				$s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET', 0, -1, 1, 0, $x*$this->k, ($this->h-$y)*$this->k, $txt);
			else
				$s=sprintf('BT %.2f %.2f Td (%s) Tj ET', $x*$this->k, ($this->h-$y)*$this->k, $txt);
			$this->_out($s);
		}

		function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
		{
			$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));

			$font_angle+=90+$txt_angle;
			$txt_angle*=M_PI/180;
			$font_angle*=M_PI/180;

			$txt_dx=cos($txt_angle);
			$txt_dy=sin($txt_angle);
			$font_dx=cos($font_angle);
			$font_dy=sin($font_angle);

			$s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET', 
					$txt_dx, $txt_dy, $font_dx, $font_dy, 
					$x*$this->k, ($this->h-$y)*$this->k, $txt);
			$this->_out($s);
		}

		function HCell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='')
		{
			//Output a cell
			$k=$this->k;
			if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
			{
				$x=$this->x;
				$ws=$this->ws;
				if($ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->AddPage($this->CurOrientation);
				$this->x=$x;
				if($ws>0)
				{
					$this->ws=$ws;
					$this->_out(sprintf('%.3f Tw', $ws*$k));
				}
			}
			if($w==0)
				$w=$this->w-$this->rMargin-$this->x;
			$s='';
			// begin change Cell function 12.08.2003 
			if($fill==1 or $border>0)
			{
				if($fill==1)
					$op=($border>0) ? 'B' : 'f';
				else
					$op='S';
				if ($border>1) {
					$s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ', $border, 
						$this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
				}
				else
					$s=sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
			}
			if(is_string($border))
			{
				$x=$this->x;
				$y=$this->y;
				if(is_int(strpos($border, 'L')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'l')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
					
				if(is_int(strpos($border, 'T')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
				else if(is_int(strpos($border, 't')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
				
				if(is_int(strpos($border, 'R')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'r')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				
				if(is_int(strpos($border, 'B')))
					$s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
				else if(is_int(strpos($border, 'b')))
					$s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
			}
			if (trim($txt)!='') {
				$cr=substr_count($txt, "\n");
				if ($cr>0) { // Multi line
					$txts = explode("\n", $txt);
					$lines = count($txts);
					//$dy=($h-2*$this->cMargin)/$lines;
					for($l=0;$l<$lines;$l++) {
						$txt=$txts[$l];
						$w_txt=$this->GetStringWidth($txt);
						if($align=='R')
							$dx=$w-$w_txt-$this->cMargin;
						elseif($align=='C')
							$dx=($w-$w_txt)/2;
						else
							$dx=$this->cMargin;

						$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
						if($this->ColorFlag)
							$s.='q '.$this->TextColor.' ';
						$s.=sprintf('BT %.2f %.2f Td (%s) Tj ET ', 
							($this->x+$dx)*$k, 
							($this->h-($this->y+.5*$h+(.7+$l-$lines/2)*$this->FontSize))*$k, 
							$txt);
						if($this->underline)
							$s.=' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
						if($this->ColorFlag)
							$s.='Q ';
						if($link)
							$this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $w_txt, $this->FontSize, $link);
					}
				}
				else { // Single line
					$w_txt=$this->GetStringWidth($txt);
					$Tz=100;
					if ($w_txt>$w-2*$this->cMargin) { // Need compression
						$Tz=($w-2*$this->cMargin)/$w_txt*100;
						$w_txt=$w-2*$this->cMargin;
					}
					if($align=='R')
						$dx=$w-$w_txt-$this->cMargin;
					elseif($align=='C')
						$dx=($w-$w_txt)/2;
					else
						$dx=$this->cMargin;
					$txt=str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
					if($this->ColorFlag)
						$s.='q '.$this->TextColor.' ';
					$s.=sprintf('q BT %.2f %.2f Td %.2f Tz (%s) Tj ET Q ', 
								($this->x+$dx)*$k, 
								($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, 
								$Tz, $txt);
					if($this->underline)
						$s.=' '.$this->_dounderline($this->x+$dx, $this->y+.5*$h+.3*$this->FontSize, $txt);
					if($this->ColorFlag)
						$s.='Q ';
					if($link)
						$this->Link($this->x+$dx, $this->y+.5*$h-.5*$this->FontSize, $w_txt, $this->FontSize, $link);
				}
			}
			// end change Cell function 12.08.2003
			if($s)
				$this->_out($s);
			$this->lasth=$h;
			if($ln>0)
			{
				//Go to next line
				$this->y+=$h;
				if($ln==1)
					$this->x=$this->lMargin;
			}
			else
				$this->x+=$w;
		}

		function SetDash($black=false,$white=false)
		{
				if($black and $white) $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
				else $s='[] 0 d';
				$this->_out($s);
		}
		
		//-------------------------FLOWING BLOCK------------------------------------//
		//EDITEI some things (added/changed)                                        //
		//The following functions were originally written by Damon Kohler           //
		//--------------------------------------------------------------------------//
		
		function saveFont()
		{
		   $saved = array();
		   $saved[ 'family' ] = $this->FontFamily;
		   $saved[ 'style' ] = $this->FontStyle;
		   $saved[ 'sizePt' ] = $this->FontSizePt;
		   $saved[ 'size' ] = $this->FontSize;
		   $saved[ 'curr' ] =& $this->CurrentFont;
		   $saved[ 'color' ] = $this->TextColor; //EDITEI
		   $saved[ 'bgcolor' ] = $this->FillColor; //EDITEI
		   $saved[ 'HREF' ] = $this->HREF; //EDITEI
		   $saved[ 'underline' ] = $this->underline; //EDITEI
		   $saved[ 'strike' ] = $this->strike; //EDITEI
		   $saved[ 'SUP' ] = $this->SUP; //EDITEI
		   $saved[ 'SUB' ] = $this->SUB; //EDITEI
		   $saved[ 'linewidth' ] = $this->LineWidth; //EDITEI
		   $saved[ 'drawcolor' ] = $this->DrawColor; //EDITEI
		   $saved[ 'is_outline' ] = $this->outline_on; //EDITEI
		
		   return $saved;
		}
		
		function restoreFont( $saved )
		{
		   $this->FontFamily = $saved[ 'family' ];
		   $this->FontStyle = $saved[ 'style' ];
		   $this->FontSizePt = $saved[ 'sizePt' ];
		   $this->FontSize = $saved[ 'size' ];
		   $this->CurrentFont =& $saved[ 'curr' ];
		   $this->TextColor = $saved[ 'color' ]; //EDITEI
		   $this->FillColor = $saved[ 'bgcolor' ]; //EDITEI
		   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
		   $this->HREF = $saved[ 'HREF' ]; //EDITEI
		   $this->underline = $saved[ 'underline' ]; //EDITEI
		   $this->strike = $saved[ 'strike' ]; //EDITEI
		   $this->SUP = $saved[ 'SUP' ]; //EDITEI
		   $this->SUB = $saved[ 'SUB' ]; //EDITEI
		   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
		   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
		   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI
		
		   if( $this->page > 0)
			  $this->_out( sprintf( 'BT /F%d %.2f Tf ET', $this->CurrentFont[ 'i' ], $this->FontSizePt ) );
		}
		
		function newFlowingBlock( $w, $h, $b = 0, $a = 'J', $f = 0 , $is_table = false )
		{
		   // cell width in points
		   if ($is_table)  $this->flowingBlockAttr[ 'width' ] = ($w * $this->k);
		   else $this->flowingBlockAttr[ 'width' ] = ($w * $this->k) - (2*$this->cMargin*$this->k);
		   // line height in user units
		   $this->flowingBlockAttr[ 'is_table' ] = $is_table;
		   $this->flowingBlockAttr[ 'height' ] = $h;
		   $this->flowingBlockAttr[ 'lineCount' ] = 0;
		   $this->flowingBlockAttr[ 'border' ] = $b;
		   $this->flowingBlockAttr[ 'align' ] = $a;
		   $this->flowingBlockAttr[ 'fill' ] = $f;
		   $this->flowingBlockAttr[ 'font' ] = array();
		   $this->flowingBlockAttr[ 'content' ] = array();
		   $this->flowingBlockAttr[ 'contentWidth' ] = 0;
		}
		
		function finishFlowingBlock($outofblock=false)
		{
		   if (!$outofblock) $currentx = $this->x; //EDITEI - in order to make the Cell method work better
		   //prints out the last chunk
		   $is_table = $this->flowingBlockAttr[ 'is_table' ];
		   $maxWidth =& $this->flowingBlockAttr[ 'width' ];
		   $lineHeight =& $this->flowingBlockAttr[ 'height' ];
		   $border =& $this->flowingBlockAttr[ 'border' ];
		   $align =& $this->flowingBlockAttr[ 'align' ];
		   $fill =& $this->flowingBlockAttr[ 'fill' ];
		   $content =& $this->flowingBlockAttr[ 'content' ];
		   $font =& $this->flowingBlockAttr[ 'font' ];
		   $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
		   $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
		
		   // set normal spacing
		   $this->_out( sprintf( '%.3f Tw', 0 ) );
		   $this->ws = 0;
		
		   // the amount of space taken up so far in user units
		   $usedWidth = 0;
		
		   // Print out each chunk
		   //EDITEI - Print content according to alignment
		   $empty = $maxWidth - $contentWidth;
		   $empty /= $this->k;
		   $b = ''; //do not use borders
		   $arraysize = count($content);
		   $margins = (2*$this->cMargin);
		   if ($outofblock)
		   {
			  $align = 'C';
			  $empty = 0;
			  $margins = $this->cMargin;
		   }
		   switch($align)
		   {
			  case 'R':
				  foreach ( $content as $k => $chunk )
				  {
					  $this->restoreFont( $font[ $k ] );
					  $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
					  // determine which borders should be used
					  $b = '';
					  if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
					  if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';
							  
					  if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
					  else $skipln = 0;
		
					  if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
					  elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
					  elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF, $currentx );//last part
					  else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
				  }
				  break;
			  case 'L':
			  case 'J':
				  foreach ( $content as $k => $chunk )
				  {
					  $this->restoreFont( $font[ $k ] );
					  $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
					  // determine which borders should be used
					  $b = '';
					  if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
					  if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';
		
					  if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
					  else $skipln = 0;
		
					  if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0) {$align='';$margins=0;} //Remove margins in this special (though often) case
		
					  if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
					  elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF );//first part
					  elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF, $currentx );//last part
					  else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF );//middle part
				  }
				  break;
			  case 'C':
				  foreach ( $content as $k => $chunk )
				  {
					  $this->restoreFont( $font[ $k ] );
					  $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
					  // determine which borders should be used
					  $b = '';
					  if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
		
					  if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
					  else $skipln = 0;
		
					  if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
					  elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
					  elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, $skipln, 'L', $fill, $this->HREF, $currentx );//last part
					  else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
				  }
				  break;
			 default: break;
		   }
		}
		
		function WriteFlowingBlock( $s , $outofblock = false )
		{
			if (!$outofblock) $currentx = $this->x; //EDITEI - in order to make the Cell method work better
			$is_table = $this->flowingBlockAttr[ 'is_table' ];
			// width of all the content so far in points
			$contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
			// cell width in points
			$maxWidth =& $this->flowingBlockAttr[ 'width' ];
			$lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
			// line height in user units
			$lineHeight =& $this->flowingBlockAttr[ 'height' ];
			$border =& $this->flowingBlockAttr[ 'border' ];
			$align =& $this->flowingBlockAttr[ 'align' ];
			$fill =& $this->flowingBlockAttr[ 'fill' ];
			$content =& $this->flowingBlockAttr[ 'content' ];
			$font =& $this->flowingBlockAttr[ 'font' ];
		
			$font[] = $this->saveFont();
			$content[] = '';
		
			$currContent =& $content[ count( $content ) - 1 ];
		
			// where the line should be cutoff if it is to be justified
			$cutoffWidth = $contentWidth;
		
			// for every character in the string
			for ( $i = 0; $i < strlen( $s ); $i++ )
			{
			   // extract the current character
			   $c = $s{$i};
			   // get the width of the character in points
			   $cw = $this->CurrentFont[ 'cw' ][ $c ] * ( $this->FontSizePt / 1000 );
		
			   if ( $c == ' ' )
			   {
				   $currContent .= ' ';
				   $cutoffWidth = $contentWidth;
				   $contentWidth += $cw;
				   continue;
			   }
			   // try adding another char
			   if ( $contentWidth + $cw > $maxWidth )
			   {
				   // it won't fit, output what we already have
				   $lineCount++;
				   //Readjust MaxSize in order to use the whole page width
				   if ($outofblock and ($lineCount == 1) ) $maxWidth = $this->pgwidth * $this->k;
				   // contains any content that didn't make it into this print
				   $savedContent = '';
				   $savedFont = array();
				   // first, cut off and save any partial words at the end of the string
				   $words = explode( ' ', $currContent );
				   
				   // if it looks like we didn't finish any words for this chunk
				   if ( count( $words ) == 1 )
				   {
					  // save and crop off the content currently on the stack
					  $savedContent = array_pop( $content );
					  $savedFont = array_pop( $font );
		
					  // trim any trailing spaces off the last bit of content
					  $currContent =& $content[ count( $content ) - 1 ];
					  $currContent = rtrim( $currContent );
				   }
				   else // otherwise, we need to find which bit to cut off
				   {
					  $lastContent = '';
					  for ( $w = 0; $w < count( $words ) - 1; $w++) $lastContent .= "{$words[ $w ]} ";
		
					  $savedContent = $words[ count( $words ) - 1 ];
					  $savedFont = $this->saveFont();
					  // replace the current content with the cropped version
					  $currContent = rtrim( $lastContent );
				   }
				   // update $contentWidth and $cutoffWidth since they changed with cropping
				   $contentWidth = 0;
				   foreach ( $content as $k => $chunk )
				   {
					  $this->restoreFont( $font[ $k ] );
					  $contentWidth += $this->GetStringWidth( $chunk ) * $this->k;
				   }
				   $cutoffWidth = $contentWidth;
				   // if it's justified, we need to find the char spacing
				   if( $align == 'J' )
				   {
					  // count how many spaces there are in the entire content string
					  $numSpaces = 0;
					  foreach ( $content as $chunk ) $numSpaces += substr_count( $chunk, ' ' );
					  // if there's more than one space, find word spacing in points
					  if ( $numSpaces > 0 ) $this->ws = ( $maxWidth - $cutoffWidth ) / $numSpaces;
					  else $this->ws = 0;
					  $this->_out( sprintf( '%.3f Tw', $this->ws ) );
				   }
				   // otherwise, we want normal spacing
				   else $this->_out( sprintf( '%.3f Tw', 0 ) );
		
				   //EDITEI - Print content according to alignment
				   if (!isset($numSpaces)) $numSpaces = 0;
				   $contentWidth -= ($this->ws*$numSpaces);
				   $empty = $maxWidth - $contentWidth - 2*($this->ws*$numSpaces);
				   $empty /= $this->k;
				   $b = ''; //do not use borders
				   /*'If' below used in order to fix "first-line of other page with justify on" bug*/
				   if($this->y+$this->divheight>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
					 {
						   $bak_x=$this->x;//Current X position
						 $ws=$this->ws;//Word Spacing
						  if($ws>0)
						  {
							 $this->ws=0;
							 $this->_out('0 Tw');
						  }
						  $this->AddPage($this->CurOrientation);
						  $this->x=$bak_x;
						  if($ws>0)
						  {
							 $this->ws=$ws;
							 $this->_out(sprintf('%.3f Tw',$ws));
						}
					 }
				   $arraysize = count($content);
				   $margins = (2*$this->cMargin);
				   if ($outofblock)
				   {
					  $align = 'C';
					  $empty = 0;
					  $margins = $this->cMargin;
				   }
				   switch($align)
				   {
					 case 'R':
						 foreach ( $content as $k => $chunk )
						 {
							 $this->restoreFont( $font[ $k ] );
							 $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
							 // determine which borders should be used
							 $b = '';
							 if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
							 if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';
		
							 if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
							 elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
							 elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 1, '', $fill, $this->HREF, $currentx );//last part
							 else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
						 }
						break;
					 case 'L':
					 case 'J':
						 foreach ( $content as $k => $chunk )
						 {
							 $this->restoreFont( $font[ $k ] );
							 $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
							 // determine which borders should be used
							 $b = '';
							 if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
							 if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';
		
							 if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0)
							 {
								 //Remove margins in this special (though often) case
								 $align='';
								 $margins=0;
							 }
		
							 if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
							 elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 0, $align, $fill, $this->HREF );//first part
							 elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 1, '', $fill, $this->HREF, $currentx );//last part
							 else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
		
							 if (!$is_table and !$outofblock and !$fill and $align=='' and $k == 0)
							 {
								 $align = 'L';
								 $margins = (2*$this->cMargin);
							 }
						 }
						 break;
					 case 'C':
						 foreach ( $content as $k => $chunk )
						 {
							 $this->restoreFont( $font[ $k ] );
							 $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
							 // determine which borders should be used
							 $b = '';
							 if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
		
							 if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
							 elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
							 elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 1, 'L', $fill, $this->HREF, $currentx );//last part
							 else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
						 }
						 break;
						 default: break;
				   }
				   // move on to the next line, reset variables, tack on saved content and current char
				   $this->restoreFont( $savedFont );
				   $font = array( $savedFont );
				   $content = array( $savedContent . $s{ $i } );
		
				   $currContent =& $content[ 0 ];
				   $contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
				   $cutoffWidth = $contentWidth;
			   }
			   // another character will fit, so add it on
			   else
			   {
				   $contentWidth += $cw;
				   $currContent .= $s{ $i };
			   }
			}
		}


	}
?>