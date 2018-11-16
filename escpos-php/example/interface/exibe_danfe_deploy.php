<?php

require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

function parteI($nfce,$printer,$aURI){
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

    $printer->text('CNPJ: '.$cnpj.' ');

    $printer->text($razao." \n");

    $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    
    echo($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    $printer->text("\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n");

}
function parteIII($nfce,$printer){

    $printer->text("\nItem Cod     |      Desc      | Qtd |  V.Unit | V.Total");

    $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        $printer->text("\n");
        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = (float)$value->prod->vUnCom;                //valor unitario
        $vProd = (float)$value->prod->vProd;   
        
        if(strlen($cProd)>=14){
            $aux = substr($cProd,0,10);
            $printer->text($aux."\n");
            $cProd = substr($cProd,10);
        }
       
        $cProd = str_pad($cProd, 14,' ');
       
        $xProd = str_pad($xProd, 16,' ');
        $qCom = str_pad($qCom, 6,' ',STR_PAD_BOTH);
        $vUnCom = str_pad($vUnCom, 10,' ',STR_PAD_BOTH);
        $vProd = str_pad($vProd, 10,' ',STR_PAD_BOTH);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        //$linha = substr($linha,0,42);
        $printer->text($linha);
        
    }

    $printer->text("\n");
}
function parteIV($nfce,$printer){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    $printer->text("\n");
    $printer->text('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    $printer->text("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
}
function parteV($nfce,$printer){
    
    $vNF = (float) $nfce->infNFe->total->ICMSTot->vNF;
   $printer->text("\n");
   $printer->text('VALOR TOTAL R$ ' . $vNF);
   $printer->text("\n");
   /*
   $printer->text('FORMA PAGAMENTO          VALOR PAGO');
    $pag = $nfce->infNFe->pag;
    $tot = $pag->count();
    
    foreach($pag as $key){
        $printer->text$key->vPag;
    }
    /*
    for ($x=0; $x<=$tot-1; $x++) {
        $tPag = (int)tipoPag($pag[0]->tPag);
        $vPag = (int) $pag[0]->vPag;
       $printer->text($tPag . '                  R$ '. $vPag);
    }
    */
   
}
function parteVII($nfce,$printer,$aURI){
    $printer->text("\n");
    $tpAmb = (int) $nfce->infNFe->ide->tpAmb;
    if ($tpAmb == 2) {
        $printer->text('EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO - SEM  VALOR FISCAL ');
    }
    $tpEmis = (int) $nfce->infNFe->ide->tpEmis;
    if ($tpEmis != 1) {
        $printer->text('EMITIDA EM AMBIENTE DE CONTINGẼNCIA');
    }
    $nNF = (float) $nfce->infNFe->ide->nNF;
    $serie = (int) $nfce->infNFe->ide->serie;
    $dhEmi = (string) $nfce->infNFe->ide->dhEmi;
    $Id = (string) $nfce->infNFe->attributes()->{'Id'};
    $chave = substr($Id, 3, strlen($Id)-3);
    $printer->text('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi . ' via  Consumidor');
    $printer->text("\n");

    $printer->text('Consulte pela chave de acesso em: ');
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    $printer->text($uri);
    $printer->text("\n");
    $printer->text('CHAVE DE ACESSO');
    $printer->text("\n");
    $printer->text($chave);

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
        $printer->text('Não foi possivel ler o documento.');
    }
    $nfe = simplexml_load_string($xml, null, LIBXML_NOCDATA);
    $protNFe = $nfe->protNFe;
    $nfce = $nfe->NFe;
    if (empty($protNFe)) {
        //NFe sem protocolo
        $nfce = $nfe;
    }
    return $nfce;
}


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
$connector = new CupsPrintConnector("bema2");
$printer = new Printer($connector);

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
        $nome_nota = $events[0]['name'];
 
        if(substr($nome_nota,0,6) == 'retsai'){

            $nfce = loadNFCe("teste_xml/".$events[0]['name']); 
            parteI($nfce,$printer,$aURI);
            
            parteIII($nfce,$printer);
            
            parteIV($nfce,$printer);
            
            parteV($nfce,$printer);
            
            parteVII($nfce,$printer,$aURI);
            
            $printer->cut();
        
        }
}

   // print_r($events);

}

// stop watching our directory
inotify_rm_watch($inoInst, $watch_id);

// close our inotify instance
fclose($inoInst);

?> 
