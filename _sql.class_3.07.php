<?
/*****************************************************************************
*	VERS�O 3.7
*	Data: 15/06/2006
*	Desenvolvido por Valder Gallo
* 	e-mail: valdergallo@yahoo.com.br
* 
* 	INCLUS�O DA FUN��O ENCODECONFIG
* 
* 	INCLUS�O DA FUN��O MOEDA
* 
* 	INCLUS�O DA FUN��O DATA
*  
* 	CORRE��ES no where dos fetch, assoc, insert, update - faltava um espa�o entre os caracteres
* 
* 	CORRE��O NA TRANSFORMA��ES DE DATA PARA BR
* 	cria��o de TABELAS
* 
* 	INCLUS�O DA FUN��O CREATETABLE PARA CRIAR TABELAS
* 
*  	CORRE��O no delete problemas se tivesse s� um valor na array de valores a 
* 	serem deletados
*  
* 
*****************************************************************************/



class _sql {
	
	/**
	 * Variavel de $sql executado para debug
	 *
	 * @var string
	 */
	public $sql;
		
	/**
	 * Retira caracteres que geram erros ao transmitir vari�veis para o Flash
	 *
	 * @param string $valueToEncode
	 * @return string
	 */
	function EncodeConfig( $valueToEncode ) {
		$chars = array( 
			'%' => '%25', 
			'&' => '%26', 
			'=' => '%3D', 
			'"' => '%22',
			 ) ;

		return strtr( $valueToEncode,  $chars ) ;
	}
		
	
	/**
	 * Retorna valores para poderem serem lidos pelo Flash
	 *
	 * @param string $sql
	 */
	function getFlash($sql){
		
		$qr = mysql_query($sql);
		
		$i =0;
		
		while ($rs = mysql_fetch_array($qr)){
		
			foreach ($rs as $key => $value) {
			    if(!is_int($key)){ 
			    	
			    	//$key = utf8_encode($key);
			    	//$value = utf8_encode($value);
			    	//$value = $this->EncodeConfig( htmlentities($value) );
					$ck = mb_detect_encoding($value, "auto");
					if($ck == "UTF-8"){	
						echo "<H3> UTF8 $ck</H3>";
						$value = $this->EncodeConfig( $value  );
						}else{
						echo "<H3> ANSI $ck</H3>";
						$value = $this->EncodeConfig(  utf8_encode($value) );
						}
					
					
					echo "&".$key."_".$i."=".$value."&";
				}
		    }
		    
		 $i++;
		 
		}
		
		$row = mysql_num_rows($qr);
		echo "&__total=".$row."&";
				
	}
	
	
	
	/**
	 * Enter description here...
	 *
	 * @param string $table
	 * @param string $campos
	 * @return number
	 */
	function insert($table, $campos){
			
	$sql = "INSERT INTO $table ";

		if(is_array($campos)){
			
			$sql .= "( ";
						
			foreach ($campos as $key => $value){
				if(!empty($value)){
					$sql .= " $key ,"; 
				}
			}
			
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
			
			$sql .= " ) VALUES ( ";
						
			foreach ($campos as $value){
				
				if(!empty($value)){ 
					switch ($value) {
					
						case is_string($value):
							//$value = utf8_encode($value);
							$sql .= " '$value',";
						break; 	
									
						case is_numeric($value):
							$sql .= " $value,";
						break;
						
															
					}
				}
				
			}
			
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);

			$sql .= " )";
			
		//echo $sql;	
		$qr = mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
		return mysql_insert_id();
		
		
		}		
		
		
	}
	
	/**
	 * Update de dados no banco
	 *
	 * @param string $table
	 * @param string $campos
	 * @param string/array $where
	 * @return number
	 */
	function update ($table, $campos, $where=''){
	
	$sql = "UPDATE $table SET";

		if(is_array($campos)){
						
			foreach ($campos as $key => $value){
				//$value = utf8_encode($value);
				$sql .= " $key = '$value' ,"; 
			}
			
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
		
		}else{
			
			$sql .= " $campos ";
			
		}
					
		if(!empty($where)) $sql .= " WHERE ";
		
			
		if(is_array($where)){	
			
			foreach ($where as $key => $value){
				$sql .= "$key like '$value' AND"; 			
			}
			
			$strCut = strlen($sql)-3;
			$sql = substr($sql,0,$strCut);
		
		}else{
			
			$sql .= " $where "; 
			
		}
			//echo $sql;
			return $qr = mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
			
		}
		
		
	/**
	 * Deleta informa��es no banco de dados
	 *
	 * @param string $table
	 * @param string $where
	 * @return number
	 */
	function delete($table,$where=""){
		
	
		$sql = "DELETE FROM $table";
		$sql .= " WHERE ";
		
		if(is_array($where)){
			
			foreach ($where as $key => $value){
				$sql .= " $key = '$value' AND"; 			
			}
			
			$strCut = strlen($sql)-3;
			$sql = substr($sql,0,$strCut);
			
		}else{
			
			$sql .= " WHERE $where";			
			
		}

		 mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
		
		return mysql_affected_rows();
			
	}
	
	
		

	/**
	 * Select padr�o com retorno de query
	 *
	 * @param string $table
	 * @param string $campos
	 * @param array/string $where
	 * @param string $order
	 * @param string $tipo
	 * @param string $limite
	 * @return query
	 */
	function select ($table, $campos, $where='', $order='', $tipo='', $limite=''){
		
		
		$sql = "SELECT ";

		if(is_array($campos)){
									
			foreach ($campos as $value){
				$sql .= "'$value' ,"; 
			}
					
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $campos ";
		
		}
		
		if ( strstr($table, "|")) {
			
			$ex = explode("|",$table);
			
			$table = $ex[0];
			
			$sql .= " FROM $table ";
			
			$jo = explode(">",$ex[0]);
			
			$sql .= " LEFT JOIN " . $jo[0];
			$sql .= " ON " . $jo[1];
						
		}else{
		
			$sql .= " FROM $table ";
		
		}
		
		if(!empty($where)) $sql .= " WHERE ";
		
			
		if(is_array($where)){
	
			foreach ($where as $key => $value){
				$sql .= "$key LIKE '$value' AND"; 			
			}
			
			$strCut = strlen($sql)-3;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $where ";
				
		}
		
		//envia sql para total rows
		$this->sql = $sql;
		
		if(!empty($order)) $sql .= " ORDER BY ";
		
		
		if(is_array($order)){
	
			foreach ($order as $value){
				$sql .= "$value ,"; 			
			}
						
			$strCut = strlen($sql)-1;
			$sql = substr($sql,0,$strCut);
	
		}else{
			
			$sql .= " $order ";
			
		}
			
		if(!empty($tipo)) $sql .= " $tipo ";
		
	
			
		if(!empty($limite)) $sql .= " LIMIT $limite ";
					
		$qr = mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
				
		return $qr;
		
	}
	
	/**
	 * Efetua um sql e retorna uma array com os valores ligados aos nomes dos campos
	 *
	 * @param string $table
	 * @param string $campos
	 * @param string $where
	 * @param string $order
	 * @param string $tipo
	 * @param string $limite
	 * @return array
	 */
	function fetch($table, $campos="*", $where="", $order="", $tipo="", $limite=""){
				
		$sql = "SELECT DISTINCT ";

		if(is_array($campos)){
									
			foreach ($campos as $value){
				$sql .= "'$value' ,"; 
			}
					
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $campos ";
		
		}
		
		if ( strstr($table, "|") ) {
			
			$fromTable = substr($table,0,strpos($table,"|"));
	
			$leftJoin = substr($table,strpos($table,"|"),strlen($table));
			
			$leftJoin = explode("|",$leftJoin);
						
			$sql .= " FROM ".$fromTable;
			
			foreach ($leftJoin as $ex){
				
				$leftEx = explode(">", $ex );
						
				@list($left_table, $on_condition) = $leftEx;
				
				if(!empty($left_table)){	
					$sql .= " LEFT JOIN " . $left_table;
				}
				if(!empty($on_condition)){	
					$sql .= " ON " . $on_condition;
				}
				
			
			}
						
		}else{
		
			$sql .= " FROM $table ";
		
		}
		
		
		if(!empty($where)) $sql .= " WHERE ";
		
			
		if(is_array($where)){
	
			foreach ($where as $key => $value){
					
				if( strstr($value,"!") ){
						$value = substr($value, 1);
						if (strtolower($value) == "empty"){
								$sql .= " $key <> '' OR $key <> 0 OR $key <> NULL  AND";
						}else if(is_int($value)){
								$sql .= " $key <> $value OR $key <> 0 OR $key <> NULL AND";	
						}else{
								$sql .= " $key <> '$value' OR $key <> 0 OR $key <> NULL  AND";	
						} 
			
				}elseif( strstr($value,"!<") ){
						$value = substr($value, 2);
						if (strtolower($value) == "empty"){
								$sql .= " $key <= ''  AND";
						}else if(is_int($value)){
								$sql .= " $key <= $value  AND";	
						}else{
								$sql .= " $key <= '$value'  AND";	
						} 		
			
				}elseif( strstr($value,"!>") ){
						$value = substr($value, 2);
						if (strtolower($value) == "empty"){
								$sql .= " $key >= ''  AND";
						}else if(is_int($value)){
								$sql .= " $key >= $value  AND";	
						}else{
								$sql .= " $key >= '$value'  AND";	
						} 	
				
						
					}else{
						if(is_int($value)){
						$sql .= " $key LIKE $value  AND"; 
						}else{
						$sql .= " $key LIKE '$value'  AND"; 
						}
				}
			}
			
			$strCut = strlen($sql)-3;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $where ";
				
		}
		
		//envia sql para total rows
		$this->sql = $sql;
			
		if(!empty($order)) $sql .= " ORDER BY ";
		
		
		if(is_array($order)){
	
			foreach ($order as $value){
				$sql .= "$value ,"; 			
			}
						
			$strCut = strlen($sql)-1;
			$sql = substr($sql,0,$strCut);
	
		}else{
			
			$sql .= " $order ";
			
		}
			
		if(!empty($tipo)) $sql .= " $tipo ";
					
		if(!empty($limite)) $sql .= " LIMIT $limite ";
								
		$qr = mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
		$rows = mysql_num_rows($qr);
		
	    if($rows){

			while ($rs = mysql_fetch_array($qr) ) {
				
				$exFetch[] = $rs; 
							
			}

		}else{
		
				$exFetch = false;
		}	

		return $exFetch;
		
	}
	
	/**
	 * Efetua um sql e retorna uma array associativa com os valores ligados aos nomes dos campos
	 *
	 * @param string $table
	 * @param string $campos
	 * @param string $where
	 * @param string $order
	 * @param string $tipo
	 * @param string $limite
	 * @return array
	 */
	function assoc($table, $campos="*", $where="", $order="", $tipo="", $limite=""){
				
		$sql = "SELECT DISTINCT ";

		if(is_array($campos)){
									
			foreach ($campos as $value){
				$sql .= "'$value' ,"; 
			}
					
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $campos ";
		
		}
		
		if ( strstr($table, "|") ) {
			
			$fromTable = substr($table,0,strpos($table,"|"));
	
			$leftJoin = substr($table,strpos($table,"|"),strlen($table));
			
			$leftJoin = explode("|",$leftJoin);
						
			$sql .= " FROM ".$fromTable;
			
			foreach ($leftJoin as $ex){
				
				$leftEx = explode(">", $ex );
						
				@list($left_table, $on_condition) = $leftEx;
				
				if(!empty($left_table)){	
					$sql .= " LEFT JOIN " . $left_table;
				}
				if(!empty($on_condition)){	
					$sql .= " ON " . $on_condition;
				}
				
			
			}
						
		}else{
		
			$sql .= " FROM $table ";
		
		}
		
		
		if(!empty($where)) $sql .= " WHERE ";
		
			
		if(is_array($where)){
	
			foreach ($where as $key => $value){
					
				if( strstr($value,"!") ){
						$value = substr($value, 1);
						if (strtolower($value) == "empty"){
								$sql .= " $key <> ''  AND";
						}else if(is_int($value)){
								$sql .= " $key <> $value  AND";	
						}else{
								$sql .= " $key <> '$value'  AND";	
						} 
			
				}elseif( strstr($value,"!<") ){
						$value = substr($value, 2);
						if (strtolower($value) == "empty"){
								$sql .= " $key <= ''  AND";
						}else if(is_int($value)){
								$sql .= " $key <= $value  AND";	
						}else{
								$sql .= " $key <= '$value'  AND";	
						} 		
			
				}elseif( strstr($value,"!>") ){
						$value = substr($value, 2);
						if (strtolower($value) == "empty"){
								$sql .= " $key >= ''  AND";
						}else if(is_int($value)){
								$sql .= " $key >= $value  AND";	
						}else{
								$sql .= " $key >= '$value'  AND";	
						} 	
				
						
					}else{
						if(is_int($value)){
						$sql .= " $key LIKE $value  AND"; 
						}else{
						$sql .= " $key LIKE '$value'  AND"; 
						}
				}
			}
			
			$strCut = strlen($sql)-3;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $where ";
				
		}
		
		//envia sql para total rows
		$this->sql = $sql;
			
		if(!empty($order)) $sql .= " ORDER BY ";
		
		
		if(is_array($order)){
	
			foreach ($order as $value){
				$sql .= "$value ,"; 			
			}
						
			$strCut = strlen($sql)-1;
			$sql = substr($sql,0,$strCut);
	
		}else{
			
			$sql .= " $order ";
			
		}
			
		if(!empty($tipo)) $sql .= " $tipo ";
					
		if(!empty($limite)) $sql .= " LIMIT $limite ";
								
		$qr = mysql_query($sql) or die($sql . " <hr> " .  mysql_error());
		$rows = mysql_num_rows($qr);
		
	    if($rows){

			while ($rs = mysql_fetch_assoc($qr) ) {
				
				$exFetch[] = $rs; 
							
			}

		}else{
		
				$exFetch = false;
		}	

		return $exFetch;
		
	}
	
	
	/**
	 * N�mero de linhas
	 *
	 * @return number
	 */
	function totalRows(){
		
		$qr = mysql_query($this->sql);
		
		if($qr){
			$rows = mysql_num_rows($qr);
		}else{
			$rows = 0;
		}
		
		return $rows;
		
	}
	
	/**
	 * Fun��o de data
	 *
	 * @param date $data
	 * @param string_date $formato
	 * @return date
	 */
	function _DATA($data,$formato="Y-m-d") {
		
		//setlocale(LC_TIME,'pt_BR','ptb'); 
		date_default_timezone_set('Brazil/East'); 
		
		$nData = strtotime($data);
		$nData = date($formato,$nData);
		
		return $nData;
		
	}

	/**
	 * Fun��o de moeda
	 *
	 * @param valor $valor
	 * @return valor
	 */
	function _MOEDA($valor){
    
		$situacao = substr_count($valor, ".");
		
		$situacao += substr_count($valor, ",");
		
		if($situacao > 0){
		
			$subs = array (",",".");
			
			$valor = str_replace($subs,"",$valor);
			
			$decimal = substr($valor, -2);
			
			$valor = substr($valor, 0, -2);
			
			$valor = $valor.".".$decimal;
			
			return $valor;
			
		} else {
			
			return $valor;
		
		}

	}

	function createTable( $tabela , $elemento="", $dropTable=0){
		
		
		//sql para criar tabela
		/*
		DROP TABLE IF EXISTS `ht_menu`;
		CREATE TABLE IF NOT EXISTS `ht_menu` (
		  `id_menu` int(10) unsigned NOT NULL auto_increment,
		  `empresa_id_empresa` int(10) unsigned NOT NULL,
		  `menu` varchar(255) collate latin1_bin default NULL,
		  `link` varchar(255) collate latin1_bin default NULL,
		  PRIMARY KEY  (`id_menu`),
		  KEY `menu_FKIndex1` (`empresa_id_empresa`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_bin
		*/
		
		$sql = "";
		
		if($dropTable){
			$sql .= "DROP TABLE IF EXISTS '$tabela' ";
		}
		
		//cria a tabela e criar o index da tabela
		$sql .= "CREATE TABLE IF NOT EXISTS '$tabela' { "
			  . "'id_$tabela' int(10) unsigned NOT NULL auto_increment, ";
		
		if( is_array($elemento) ){
									
			foreach ($elemento as $key => $value){
				switch ($value){
					
					case strstr('varchar',$value):
						$sql .= " '$key' varchar(255) collate latin1_bin default NULL ,"; 
					break;
					
					case strstr('int',$value):
						$sql .= " '$key' int(10) collate latin1_bin default NULL ,"; 
					break;
					
					case strstr('timestamp',$value):
						$sql .= " '$key' timestamp default NOW() ,"; 
					break;
										
					default:
						$sql .= " '$key' longtext collate latin1_bin default NULL ,"; 
					}					
				
				}
			
			$strCut = strlen($sql) -1;
			$sql = substr($sql,0,$strCut);
		
		}else{

			$sql .= " $campos ";
		
		}
		
		$qr = mysql_query($sql) or die(mysql_error());
		
	}
		
}	
?>
