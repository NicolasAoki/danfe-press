<?php

require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

function parteI($nfce,$aURI){
    $razao = (string)$nfce->infNFe->emit->xNome;
    $cnpj = (string)$nfce->infNFe->emit->CNPJ;
    $ie = (string)$nfce->infNFe->emit->IE;
    $im = (string)$nfce->infNFe->emit->IM;
    $log = (string)$nfce->infNFe->emit->enderEmit->xLgr;
    $nro = (string)$nfce->infNFe->emit->enderEmit->nro;
    $bairro = (string)$nfce->infNFe->emit->enderEmit->xBairro;
    $mun = (string)$nfce->infNFe->emit->enderEmit->xMun;
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }

    echo ('CNPJ: '.$cnpj.' ');

    echo($razao." \n");

    echo ($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    

    echo "\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n";

}
function parteIII($nfce,$printer){

    echo("\nItem Cod     |      Desc      | Qtd |  V.Unit | V.Total");

    $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        echo ("\n");
        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = (float)$value->prod->vUnCom;                //valor unitario
        $vProd = (float)$value->prod->vProd;   
        
        if(strlen($cProd)>=14){
            $aux = substr($cProd,0,10);
            echo $aux."\n";
            $cProd = substr($cProd,10);
        }
       
        $cProd = str_pad($cProd, 14,' ');
       
        $xProd = str_pad($xProd, 16,' ');
        $qCom = str_pad($qCom, 6,' ',STR_PAD_BOTH);
        $vUnCom = str_pad($vUnCom, 10,' ',STR_PAD_BOTH);
        $vProd = str_pad($vProd, 10,' ',STR_PAD_BOTH);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        //$linha = substr($linha,0,42);
        echo $linha;
        
    }

    echo "\n";
}
function parteIV($nfce,$printer){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    echo("\n");
    echo('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    echo("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
}
function parteV($nfce,$printer){
    
    $vNF = (float) $nfce->infNFe->total->ICMSTot->vNF;
   echo("\n");
   echo('VALOR TOTAL R$ ' . $vNF);
   echo("\n");
   /*
   echo('FORMA PAGAMENTO          VALOR PAGO');
    $pag = $nfce->infNFe->pag;
    $tot = $pag->count();
    
    foreach($pag as $key){
        echo $key->vPag;
    }
    /*
    for ($x=0; $x<=$tot-1; $x++) {
        $tPag = (int)tipoPag($pag[0]->tPag);
        $vPag = (int) $pag[0]->vPag;
       echo($tPag . '                  R$ '. $vPag);
    }
    */
   
}
function parteVII($nfce,$printer,$aURI){
    echo("\n");
    $tpAmb = (int) $nfce->infNFe->ide->tpAmb;
    if ($tpAmb == 2) {
        echo('EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO - SEM  VALOR FISCAL ');
    }
    $tpEmis = (int) $nfce->infNFe->ide->tpEmis;
    if ($tpEmis != 1) {
        echo('EMITIDA EM AMBIENTE DE CONTINGẼNCIA');
    }
    $nNF = (float) $nfce->infNFe->ide->nNF;
    $serie = (int) $nfce->infNFe->ide->serie;
    $dhEmi = (string) $nfce->infNFe->ide->dhEmi;
    $Id = (string) $nfce->infNFe->attributes()->{'Id'};
    $chave = substr($Id, 3, strlen($Id)-3);
    echo('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi . ' via  Consumidor');
    echo("\n");

    echo('Consulte pela chave de acesso em: ');
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    echo($uri);
    echo("\n");
    echo('CHAVE DE ACESSO');
    echo("\n");
    echo($chave);

}
function tipoPag($tPag){
    $aPag = [
        '01' => 'Dinheiro',
        '02' => 'Cheque',
        '03' => 'Cartao de Credito',
        '04' => 'Cartao de Debito',
        '05' => 'Credito Loja',
        '10' => 'Vale Alimentacao',
        '11' => 'Vale Refeicao',
        '12' => 'Vale Presente',
        '13' => 'Vale Combustivel',
        '99' => 'Outros'
    ];
    if (array_key_exists($tPag, $aPag)) {
        return $aPag[$tPag];
    }
    return '';
}
function loadNFCe($nfcexml){
    $xml = $nfcexml;
    if (is_file($nfcexml)) {
        $xml = @file_get_contents($nfcexml);
    }
    if (empty($xml)) {
        echo('Não foi possivel ler o documento.');
    }
    $nfe = simplexml_load_string($xml, null, LIBXML_NOCDATA);
    $protNFe = $nfe->protNFe;
    $nfce = $nfe->NFe;
    if (empty($protNFe)) {
        //NFe sem protocolo
        $nfce = $nfe;
    }
    print_r($nfce->infNFeSupl);
    return $nfce;
}
function leArquivo($pathFile = ''){
    if ($pathFile == '') {
        $msg = "Um caminho para o arquivo deve ser passado!!";

    }
    if (! is_file($pathFile)) {
        $msg = "O arquivo indicado não foi localizado!! $pathFile";

    }
    if (! is_readable($pathFile)) {
        $msg = "O arquivo indicado não pode ser lido. Permissões!! $pathFile";

    }
    return file_get_contents($pathFile);
}
/*
function constroiDanfe(
    $docXML = '',
    $sOrientacao = '',
    $sPapel = '',
    $sPathLogo = '',
    $sDestino = 'I',
    $sDirPDF = '',
    $fonteDANFE = '',
    $mododebug = 2
) {
    $this->orientacao   = $sOrientacao;
    $this->papel        = $sPapel;
    $this->pdf          = '';
    $this->xml          = $docXML;
    $this->logomarca    = $sPathLogo;
    $this->destino      = $sDestino;
    $this->pdfDir       = $sDirPDF;
    // verifica se foi passa a fonte a ser usada
    if (empty($fonteDANFE)) {
        $this->fontePadrao = 'Times';
    } else {
        $this->fontePadrao = $fonteDANFE;
    }
    //se for passado o xml
    if (! empty($this->xml)) {
        $this->dom = new DomDocumentNFePHP();
        $this->dom->loadXML($this->xml);
        $this->nfeProc    = $this->dom->getElementsByTagName("nfeProc")->item(0);
        $this->infNFe     = $this->dom->getElementsByTagName("infNFe")->item(0);
        $this->ide        = $this->dom->getElementsByTagName("ide")->item(0);
        $this->entrega    = $this->dom->getElementsByTagName("entrega")->item(0);
        $this->retirada   = $this->dom->getElementsByTagName("retirada")->item(0);
        $this->emit       = $this->dom->getElementsByTagName("emit")->item(0);
        $this->dest       = $this->dom->getElementsByTagName("dest")->item(0);
        $this->enderEmit  = $this->dom->getElementsByTagName("enderEmit")->item(0);
        $this->enderDest  = $this->dom->getElementsByTagName("enderDest")->item(0);
        $this->det        = $this->dom->getElementsByTagName("det");
        $this->cobr       = $this->dom->getElementsByTagName("cobr")->item(0);
        $this->dup        = $this->dom->getElementsByTagName('dup');
        $this->ICMSTot    = $this->dom->getElementsByTagName("ICMSTot")->item(0);
        $this->ISSQNtot   = $this->dom->getElementsByTagName("ISSQNtot")->item(0);
        $this->transp     = $this->dom->getElementsByTagName("transp")->item(0);
        $this->transporta = $this->dom->getElementsByTagName("transporta")->item(0);
        $this->veicTransp = $this->dom->getElementsByTagName("veicTransp")->item(0);
        $this->reboque    = $this->dom->getElementsByTagName("reboque")->item(0);
        $this->infAdic    = $this->dom->getElementsByTagName("infAdic")->item(0);
        $this->compra     = $this->dom->getElementsByTagName("compra")->item(0);
        $this->tpEmis     = $this->ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
        $this->tpImp      = $this->ide->getElementsByTagName("tpImp")->item(0)->nodeValue;
        $this->infProt    = $this->dom->getElementsByTagName("infProt")->item(0);
        //valida se o XML é uma NF-e modelo 55, pois não pode ser 65 (NFC-e)
        if ($this->pSimpleGetValue($this->ide, "mod") != '55') {
            echo("O xml do DANFE deve ser uma NF-e modelo 55");
        }
    }
} //fim __construct*/
function montaDANFE(
    $orientacao = '',
    $papel = 'A4',
    $logoAlign = 'C',
    $situacaoExterna = NFEPHP_SITUACAO_EXTERNA_NONE,
    $classPdf = false,
    $depecNumReg = '',
    $margSup = 2,
    $margEsq = 2,
    $margInf = 2
) {
    //se a orientação estiver em branco utilizar o padrão estabelecido na NF
    if ($orientacao == '') {
        if ($this->tpImp == '1') {
            $orientacao = 'P';
        } else {
            $orientacao = 'L';
        }
    }
    $this->orientacao = $orientacao;
    $this->pAdicionaLogoPeloCnpj();
    $this->papel = $papel;
    $this->logoAlign = $logoAlign;
    $this->situacao_externa = $situacaoExterna;
    $this->numero_registro_dpec = $depecNumReg;
    //instancia a classe pdf
    if ($classPdf) {
        $this->pdf = $classPdf;
    } else {
        $this->pdf = new PdfNFePHP($this->orientacao, 'mm', $this->papel);
    }
    //margens do PDF, em milímetros. Obs.: a margem direita é sempre igual à
    //margem esquerda. A margem inferior *não* existe na FPDF, é definida aqui
    //apenas para controle se necessário ser maior do que a margem superior
    // posição inicial do conteúdo, a partir do canto superior esquerdo da página
    $xInic = $margEsq;
    $yInic = $margSup;
    if ($this->orientacao == 'P') {
        if ($papel == 'A4') {
            $maxW = 210;
            $maxH = 297;
        }
    } else {
        if ($papel == 'A4') {
            $maxH = 210;
            $maxW = 297;
            //se paisagem multiplica a largura do canhoto pela quantidade de canhotos
            $this->wCanhoto *= $this->qCanhoto;
        }
    }
    //total inicial de paginas
    $totPag = 1;
    //largura imprimivel em mm: largura da folha menos as margens esq/direita
    $this->wPrint = $maxW-($margEsq*2);
    //comprimento (altura) imprimivel em mm: altura da folha menos as margens
    //superior e inferior
    $this->hPrint = $maxH-$margSup-$margInf;
    // estabelece contagem de paginas
    $this->pdf->AliasNbPages();
    // fixa as margens
    $this->pdf->SetMargins($margEsq, $margSup);
    $this->pdf->SetDrawColor(0, 0, 0);
    $this->pdf->SetFillColor(255, 255, 255);
    // inicia o documento
    $this->pdf->Open();
    // adiciona a primeira página
    $this->pdf->AddPage($this->orientacao, $this->papel);
    $this->pdf->SetLineWidth(0.1);
    $this->pdf->SetTextColor(0, 0, 0);
    //##################################################################
    // CALCULO DO NUMERO DE PAGINAS A SEREM IMPRESSAS
    //##################################################################
    //Verificando quantas linhas serão usadas para impressão das duplicatas
    $linhasDup = 0;
    if (($this->dup->length > 0) && ($this->dup->length <= 7)) {
        $linhasDup = 1;
    } elseif (($this->dup->length > 7) && ($this->dup->length <= 14)) {
        $linhasDup = 2;
    } elseif (($this->dup->length > 14) && ($this->dup->length <= 21)) {
        $linhasDup = 3;
    } elseif ($this->dup->length > 21) {
        // chinnonsantos 11/05/2016: Limite máximo de impressão de duplicatas na NFe,
        // só vai ser exibito as 21 primeiras duplicatas (parcelas de pagamento),
        // se não oculpa espaço d+, cada linha comporta até 7 duplicatas.
        $linhasDup = 3;
    }
    //verifica se será impressa a linha dos serviços ISSQN
    $linhaISSQN = 0;
    if ((isset($this->ISSQNtot)) && ($this->pSimpleGetValue($this->ISSQNtot, 'vServ') > 0)) {
        $linhaISSQN = 1;
    }
    //calcular a altura necessária para os dados adicionais
    if ($this->orientacao == 'P') {
        $this->wAdic = round($this->wPrint*0.66, 0);
    } else {
        $this->wAdic = round(($this->wPrint-$this->wCanhoto)*0.5, 0);
    }
    $fontProduto = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
    $this->textoAdic = '';
    if (isset($this->retirada)) {
        $txRetCNPJ = ! empty($this->retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue :
            '';
        $txRetxLgr = ! empty($this->retirada->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("xLgr")->item(0)->nodeValue :
            '';
        $txRetnro = ! empty($this->retirada->getElementsByTagName("nro")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("nro")->item(0)->nodeValue :
            's/n';
        $txRetxCpl = $this->pSimpleGetValue($this->retirada, "xCpl", " - ");
        $txRetxBairro = ! empty($this->retirada->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("xBairro")->item(0)->nodeValue :
            '';
        $txRetxMun = ! empty($this->retirada->getElementsByTagName("xMun")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("xMun")->item(0)->nodeValue :
            '';
        $txRetUF = ! empty($this->retirada->getElementsByTagName("UF")->item(0)->nodeValue) ?
            $this->retirada->getElementsByTagName("UF")->item(0)->nodeValue :
            '';
        $this->textoAdic .= "LOCAL DE RETIRADA : ".
                $txRetCNPJ.
                '-' .
                $txRetxLgr .
                ', ' .
                $txRetnro .
                ' ' .
                $txRetxCpl .
                ' - ' .
                $txRetxBairro .
                ' ' .
                $txRetxMun .
                ' - ' .
                $txRetUF .
                "\r\n";
    }
    //dados do local de entrega da mercadoria
    if (isset($this->entrega)) {
        $txRetCNPJ = ! empty($this->entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
        $txRetxLgr = ! empty($this->entrega->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
        $txRetnro = ! empty($this->entrega->getElementsByTagName("nro")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("nro")->item(0)->nodeValue : 's/n';
        $txRetxCpl = $this->pSimpleGetValue($this->entrega, "xCpl", " - ");
        $txRetxBairro = ! empty($this->entrega->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
        $txRetxMun = ! empty($this->entrega->getElementsByTagName("xMun")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("xMun")->item(0)->nodeValue : '';
        $txRetUF = ! empty($this->entrega->getElementsByTagName("UF")->item(0)->nodeValue) ?
            $this->entrega->getElementsByTagName("UF")->item(0)->nodeValue : '';
        if ($this->textoAdic != '') {
            $this->textoAdic .= ". \r\n";
        }
        $this->textoAdic .= "LOCAL DE ENTREGA : ".$txRetCNPJ.'-'.$txRetxLgr.', '.$txRetnro.' '.$txRetxCpl.
           ' - '.$txRetxBairro.' '.$txRetxMun.' - '.$txRetUF."\r\n";
    }
    //informações adicionais
    $this->textoAdic .= $this->pGeraInformacoesDasNotasReferenciadas();
    if (isset($this->infAdic)) {
        $i = 0;
        if ($this->textoAdic != '') {
            $this->textoAdic .= ". \r\n";
        }
        $this->textoAdic .= ! empty($this->infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue) ?
            'Inf. Contribuinte: ' .
            trim($this->pAnfavea($this->infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue)) : '';
        $infPedido = $this->pGeraInformacoesDaTagCompra();
        if ($infPedido != "") {
            $this->textoAdic .= $infPedido;
        }
        $this->textoAdic .= $this->pSimpleGetValue($this->dest, "email", ' Email do Destinatário: ');
        $this->textoAdic .= ! empty($this->infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue) ?
            "\r\n Inf. fisco: " .
            trim($this->infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue) : '';
        $obsCont = $this->infAdic->getElementsByTagName("obsCont");
        if (isset($obsCont)) {
            foreach ($obsCont as $obs) {
                $campo =  $obsCont->item($i)->getAttribute("xCampo");
                $xTexto = ! empty($obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue) ?
                    $obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue : '';
                $this->textoAdic .= "\r\n" . $campo . ':  ' . trim($xTexto);
                $i++;
            }
        }
    }
    //INCLUSO pela NT 2013.003 Lei da Transparência
    //verificar se a informação sobre o valor aproximado dos tributos
    //já se encontra no campo de informações adicionais
    if ($this->exibirValorTributos) {
        $flagVTT = strpos(strtolower(trim($this->textoAdic)), 'valor');
        $flagVTT = $flagVTT || strpos(strtolower(trim($this->textoAdic)), 'vl');
        $flagVTT = $flagVTT && strpos(strtolower(trim($this->textoAdic)), 'aprox');
        $flagVTT = $flagVTT && (strpos(strtolower(trim($this->textoAdic)), 'trib') ||
                strpos(strtolower(trim($this->textoAdic)), 'imp'));
        $vTotTrib = $this->pSimpleGetValue($this->ICMSTot, 'vTotTrib');
        if ($vTotTrib != '' && !$flagVTT) {
            $this->textoAdic .= "\n Valor Aproximado dos Tributos : R$ " . number_format($vTotTrib, 2, ",", ".");
        }
    }
    //fim da alteração NT 2013.003 Lei da Transparência
    $this->textoAdic = str_replace(";", "\n", $this->textoAdic);
    $alinhas = explode("\n", $this->textoAdic);
    $numlinhasdados = 0;
    foreach ($alinhas as $linha) {
        $numlinhasdados += $this->pGetNumLines($linha, $this->wAdic, $fontProduto);
    }
    $hdadosadic = round(($numlinhasdados+3) * $this->pdf->FontSize, 0);
    if ($hdadosadic < 10) {
        $hdadosadic = 10;
    }
    //altura disponivel para os campos da DANFE
    $hcabecalho = 47;//para cabeçalho
    $hdestinatario = 25;//para destinatario
    $hduplicatas = 12;//para cada grupo de 7 duplicatas
    $himposto = 18;// para imposto
    $htransporte = 25;// para transporte
    $hissqn = 11;// para issqn
    $hfooter = 5;// para rodape
    $hCabecItens = 4;//cabeçalho dos itens
    //alturas disponiveis para os dados
    $hDispo1 = $this->hPrint - 10 - ($hcabecalho +
        $hdestinatario + ($linhasDup * $hduplicatas) + $himposto + $htransporte +
        ($linhaISSQN * $hissqn) + $hdadosadic + $hfooter + $hCabecItens +
        $this->pSizeExtraTextoFatura());
    if ($this->orientacao == 'P') {
        $hDispo1 -= 23 * $this->qCanhoto;//para canhoto
        $w = $this->wPrint;
    } else {
        $hcanhoto = $this->hPrint;//para canhoto
        $w = $this->wPrint - $this->wCanhoto;
    }
    $hDispo2 = $this->hPrint - 10 - ($hcabecalho + $hfooter + $hCabecItens)-4;
    //Contagem da altura ocupada para impressão dos itens
    $fontProduto = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
    $i = 0;
    $numlinhas = 0;
    $hUsado = $hCabecItens;
    $w2 = round($w*0.28, 0);
    $hDispo = $hDispo1;
    $totPag = 1;
    while ($i < $this->det->length) {
        $texto = $this->pDescricaoProduto($this->det->item($i));
        $numlinhas = $this->pGetNumLines($texto, $w2, $fontProduto);
        $hUsado += round(($numlinhas * $this->pdf->FontSize) + ($numlinhas * 0.5), 2);
        if ($hUsado > $hDispo) {
            $totPag++;
            $hDispo = $hDispo2;
            $hUsado = $hCabecItens;
            // Remove canhoto para páginas secundárias em modo paisagem ('L')
            $w2 = round($this->wPrint*0.28, 0);
            $i--; // decrementa para readicionar o item que não coube nessa pagina na outra.
        }
        $i++;
    } //fim da soma das areas de itens usadas
    $qtdeItens = $i; //controle da quantidade de itens no DANFE
    //montagem da primeira página
    $pag = 1;
    $x = $xInic;
    $y = $yInic;
    //coloca o(s) canhoto(s) da NFe
    if ($this->orientacao == 'P') {
        for ($i = 1; $i <= $this->qCanhoto; $i++) {
            $y = $this->pCanhoto($x, $y);
        }
    } else {
        for ($i = 1; $i <= $this->qCanhoto; $i++) {
            $this->pCanhoto($x, $y);
            $x = 25 * $i;
        }
    }
    //coloca o cabeçalho
    $y = $this->pCabecalhoDANFE($x, $y, $pag, $totPag);
    //coloca os dados do destinatário
    $y = $this->pDestinatarioDANFE($x, $y+1);
    //coloca os dados das faturas
    $y = $this->pFaturaDANFE($x, $y+1);
    //coloca os dados dos impostos e totais da NFe
    $y = $this->pImpostoDANFE($x, $y+1);
    //coloca os dados do trasnporte
    $y = $this->pTransporteDANFE($x, $y+1);
    //itens da DANFE
    $nInicial = 0;
    $y = $this->pItensDANFE($x, $y+1, $nInicial, $hDispo1, $pag, $totPag, $hCabecItens);
    //coloca os dados do ISSQN
    if ($linhaISSQN == 1) {
        $y = $this->pIssqnDANFE($x, $y+4);
    } else {
        $y += 4;
    }
    //coloca os dados adicionais da NFe
    $y = $this->pDadosAdicionaisDANFE($x, $y, $hdadosadic);
    //coloca o rodapé da página
    if ($this->orientacao == 'P') {
        $this->pRodape($xInic, $y-1);
    } else {
        $this->pRodape($xInic, $this->hPrint + 1);
    }
    //loop para páginas seguintes
    for ($n = 2; $n <= $totPag; $n++) {
        // fixa as margens
        $this->pdf->SetMargins($margEsq, $margSup);
        //adiciona nova página
        $this->pdf->AddPage($this->orientacao, $this->papel);
        //ajusta espessura das linhas
        $this->pdf->SetLineWidth(0.1);
        //seta a cor do texto para petro
        $this->pdf->SetTextColor(0, 0, 0);
        // posição inicial do relatorio
        $x = $xInic;
        $y = $yInic;
        //coloca o cabeçalho na página adicional
        $y = $this->pCabecalhoDANFE($x, $y, $n, $totPag);
        //coloca os itens na página adicional
        $y = $this->pItensDANFE($x, $y+1, $nInicial, $hDispo2, $n, $totPag, $hCabecItens);
        //coloca o rodapé da página
        if ($this->orientacao == 'P') {
            $this->pRodape($xInic, $y + 4);
        } else {
            $this->pRodape($xInic, $this->hPrint + 4);
        }
        //se estiver na última página e ainda restar itens para inserir, adiciona mais uma página
        if ($n == $totPag && $this->qtdeItensProc < $qtdeItens) {
            $totPag++;
        }
    }
    //retorna o ID na NFe
    if ($classPdf!==false) {
        $aR = array(
         'id'=>str_replace('NFe', '', $this->infNFe->getAttribute("Id")),
         'classe_PDF'=>$this->pdf);
        return $aR;
    } else {
        return str_replace('NFe', '', $this->infNFe->getAttribute("Id"));
    }
}//fim da função montaDANFE

function printDANFE($nome = '', $destino = 'I', $printer = ''){

        $arq = $this->pdf->Output($nome, $destino);
        if ($destino == 'S') {
            //aqui pode entrar a rotina de impressão direta
        }
        return $arq;

} //fim função printDANFE

$nfce = '';
        $protNFe = '';
        $printer='';
        $da = [];
        $totItens = 0;
        $uri = '';
        $aURI = [
            'AC' => 'http://sefaznet.ac.gov.br/nfce/consulta.xhtml',
            'AM' => 'http://sistemas.sefaz.am.gov.br/nfceweb/formConsulta.do',
            'BA' => 'http://nfe.sefaz.ba.gov.br/servicos/nfce/Modulos/Geral/NFCEC_consulta_chave_acesso.aspx',
            'MT' => 'https://www.sefaz.mt.gov.br/nfce/consultanfce',
            'MA' => 'http://www.nfce.sefaz.ma.gov.br/portal/consultaNFe.do?method=preFilterCupom&',
            'PA' => 'https://appnfc.sefa.pa.gov.br/portal/view/consultas/nfce/consultanfce.seam',
            'PB' => 'https://www.receita.pb.gov.br/ser/servirtual/documentos-fiscais/nfc-e/consultar-nfc-e',
            'PR' => 'http://www.sped.fazenda.pr.gov.br/modules/conteudo/conteudo.php?conteudo=100',
            'RJ' => 'http://www4.fazenda.rj.gov.br/consultaDFe/paginas/consultaChaveAcesso.faces',
            'RS' => 'https://www.sefaz.rs.gov.br/NFE/NFE-COM.aspx',
            'RO' => 'http://www.nfce.sefin.ro.gov.br/home.jsp',
            'RR' => 'https://www.sefaz.rr.gov.br/nfce/servlet/wp_consulta_nfce',
            'SE' => 'http://www.nfce.se.gov.br/portal/portalNoticias.jsp?jsp=barra-menu/servicos/consultaDANFENFCe.htm',
            'SP' => 'https://www.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaPublica.aspx'
        ];
        $notas_xml = array();
        
//foreach(glob('teste_xml/*xml') as $filename){
 
  //  array_push($notas_xml, $filename);
/*

    try {
        $nfce = loadNFCe($filename);
        //$nfce = loadNFCe('teste_nota.xml');
        parteI($nfce,$aURI);
        parteIII($nfce,$printer);
        parteIV($nfce,$printer);
        parteV($nfce,$printer);
        parteVII($nfce,$printer,$aURI);

    } catch (Exception $e) {
        echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
    }
   // $danfe = constroiDanfe($docxml, 'P', 'A4', '', 'I', '');
    //$id = montaDANFE();
    
    //$teste = $danfe->printDANFE($id.'.pdf', 'F');
    
*/
//}
$dirWatch = 'teste_xml';

// Open an inotify instance
$inoInst = inotify_init();

// this is needed so inotify_read while operate in non blocking mode
stream_set_blocking($inoInst, 0);

// watch if a file is created or deleted in our directory to watch
$watch_id = inotify_add_watch($inoInst, $dirWatch, IN_ALL_EVENTS);

// not the best way but sufficient for this example :-)
while(true){
    
    // read events (
    // which is non blocking because of our use of stream_set_blocking
    $events = inotify_read($inoInst);

    //mask '2' evento que verifica se o arquivo esta sendo copiado para pasta
    if ($events[0]['mask'] === 2){
        echo $events[0]['name'] . "\n";
        $nfce = loadNFCe("teste_xml/".$events[0]['name']); 
        parteI($nfce,$aURI);
        parteIII($nfce,$printer);
        parteIV($nfce,$printer);
        parteV($nfce,$printer);
        parteVII($nfce,$printer,$aURI);
        echo "\n------------------------------------------------------------------------\n";
    }
     /*   
    if ($events[0]['wd'] === $watch_id){
        $nfce = loadNFCe('teste_nota.xml');
        parteI($nfce,$aURI);
        parteIII($nfce,$printer);
        parteIV($nfce,$printer);
        parteV($nfce,$printer);
        parteVII($nfce,$printer,$aURI);
        echo "\n------------------------------------------------------------------------\n";
    }
    // output data
   // print_r($events);
*/
}

// stop watching our directory
inotify_rm_watch($inoInst, $watch_id);

// close our inotify instance
fclose($inoInst);

?> 
