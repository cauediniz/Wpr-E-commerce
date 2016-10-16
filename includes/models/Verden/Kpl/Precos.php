<?php
/**
 * 
 * Classe para processar as atualiza��es de pre�o no ERP KPL - �bacos 
 * 
 * @author    Tito Junior 
 * 
 */

class Model_Verden_Kpl_Precos extends Model_Verden_Kpl_KplWebService {
	
	/*
	 * Instancia Webservice Magento
	*/
	private $_magento;
	
	/*
	 * Instancia Webservice KPL
	*/
	private $_kpl;
	
	/**
	 * 
	 * construtor.
	 * @param int $cli_id
	 * @param int $empwh_id
	 */
	function __construct() {
		
		if (empty ( $this->_kpl )) {
			$this->_kpl = new Model_Verden_Kpl_KplWebService (  );
		}
	
	}

	/**
	 * 
	 * M�todo para atualiza��o de pre�o dos produtos	 
	 * @throws RuntimeException
	 */
	private function _atualizaPreco ( $dados_precos ) {
				
        $idProduto = $dados_precos['product_id'];
		$produto =  array(
							'price' => $dados_precos ['PrecoTabela'],
							'special_price' => $dados_precos ['PrecoPromocional'],
						); 

		$this->_magento->atualizaProduto($idProduto, $produto);
	
	}

	/**
	 * 
	 * Buscar Produto.
	 * @param string $sku
	 * @param string $part_number
	 * @param int $ean_proprio
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function buscaProduto ( $sku, $part_number, $ean_proprio ) {		
		
		// verificar se o produto existe
		
		// BUSCAR PRODUTO MAGENTO		
	
	}

	/**
	 * 
	 * Processar cadastro de pre�os via webservice.
	 * @param string $guid
	 * @param array $request
	 */
	function ProcessaPrecosWebservice ( $request ) {

		// produtos
		$erro = null;
		
		// cole��o de erros, no formato $array_erros[$registro][] = erro
		$array_erros = array ();
		$array_erro_principal = array ();
		$array_precos = array ();
		
		if ( ! is_array ( $request ['DadosPreco'] [0] ) ) {
					
			$array_precos [0] ['ProtocoloPreco'] = $request ['DadosPreco'] ['ProtocoloPreco'];
			$array_precos [0] ['CodigoProduto'] = $request ['DadosPreco'] ['CodigoProduto'];
			$array_precos [0] ['CodigoProdutoPai'] = $request ['DadosPreco'] ['CodigoProdutoPai'];
			$array_precos [0] ['CodigoProdutoAbacos'] = $request ['DadosPreco'] ['CodigoProdutoAbacos'];
			$array_precos [0] ['NomeLista'] = $request ['DadosPreco'] ['NomeLista'];
			$array_precos [0] ['PrecoTabela'] = $request ['DadosPreco'] ['PrecoTabela'];
			$array_precos [0] ['PrecoPromocional'] = $request ['DadosPreco'] ['PrecoPromocional'];
			$array_precos [0] ['DataInicioPromocao'] = $request ['DadosPreco'] ['DataInicioPromocao'];
			$array_precos [0] ['DataTerminoPromocao'] = $request ['DadosPreco'] ['DataTerminoPromocao'];
			$array_precos [0] ['DescontoMaximoProduto'] = $request ['DadosPreco'] ['DescontoMaximoProduto'];
			$array_precos [0] ['CodigoProdutoParceiro'] = $request ['DadosPreco'] ['CodigoProdutoParceiro'];
			
		} else {
			
			foreach ( $request ["DadosPreco"] as $i => $d ) {
				
			$array_precos [0] ['ProtocoloPreco'] = $d ['ProtocoloPreco'];
			$array_precos [0] ['CodigoProduto'] = $d ['CodigoProduto'];
			$array_precos [0] ['CodigoProdutoPai'] = $d ['CodigoProdutoPai'];
			$array_precos [0] ['CodigoProdutoAbacos'] = $d ['CodigoProdutoAbacos'];
			$array_precos [0] ['NomeLista'] = $d ['NomeLista'];
			$array_precos [0] ['PrecoTabela'] = $d ['PrecoTabela'];
			$array_precos [0] ['PrecoPromocional'] = $d ['PrecoPromocional'];
			$array_precos [0] ['DataInicioPromocao'] = $d ['DataInicioPromocao'];
			$array_precos [0] ['DataTerminoPromocao'] = $d ['DataTerminoPromocao'];
			$array_precos [0] ['DescontoMaximoProduto'] = $d ['DescontoMaximoProduto'];
			$array_precos [0] ['CodigoProdutoParceiro'] = $d ['CodigoProdutoParceiro'];
			}
		}
		
		$qtdPrecos = count($array_precos);
		
		echo PHP_EOL;
		echo "Precos encontrados para integracao: " . $qtdPrecos . PHP_EOL;
		echo PHP_EOL;
		
		echo "Conectando ao WebService Magento... " . PHP_EOL;
		$this->_magento = new Model_Verden_Magento_Precos();
		echo "Conectado!" . PHP_EOL;
		echo PHP_EOL;
		
		// Percorrer array de pre�os
		foreach ( $array_precos as $indice => $dados_precos ) {
			$erros_precos = 0;
			$array_inclui_precos = array ();			
			
			if ( empty ( $dados_precos ['PrecoTabela'] ) ) {
				echo "Preco do produto {$dados_precos['CodigoProduto']}: Dados obrigat�rios n�o preenchidos" . PHP_EOL;
				$array_erro [$indice] = "Produto {$dados_precos['CodigoProduto']}: Dados obrigat�rios n�o preenchidos" . PHP_EOL;
				$erros_precos ++;
			}
			if ( $erros_precos == 0 ) {
				
				try {
					// Localizar Produto para atualizar pre�o
					echo "Buscando cadastro do produto " . $dados_precos['CodigoProduto'] . PHP_EOL;
					$produto = $this->_magento->buscaProduto($dados_precos['CodigoProduto']);
					if ( !empty ( $produto ) ) {
						echo "Atualizando Preco " . $dados_precos['CodigoProduto'] . PHP_EOL;
						$dados_precos['product_id'] = $produto; // ID do Produto na Loja Magento
						$this->_atualizaPreco( $dados_precos );
						echo "Preco atualizado. " . PHP_EOL;
						
					}else{
						throw new RuntimeException( 'Produto n�o encontrado' );
					} 
										
					$this->_kpl->confirmarPrecosDisponiveis ( $dados_precos ['ProtocoloPreco'] );
					echo "Protocolo Preco: {$dados_precos ['ProtocoloPreco']} enviado com sucesso" . PHP_EOL;		

				} catch ( Exception $e ) {
					echo "Erro ao importar preco {$dados_precos['CodigoProduto']}: " . $e->getMessage() . PHP_EOL;
					echo PHP_EOL;
					$array_erro [$indice] = "Erro ao importar preco {$dados_precos['CodigoProduto']}: " . $e->getMessage() . PHP_EOL;
				}
			
			}
		}		
		
		// finaliza sess�o Magento
		$this->_magento->_encerraSessao();
		
		if(is_array($array_erro)){
			$array_retorno = $array_erro;
		}
		return $array_retorno;
	
	}

}

