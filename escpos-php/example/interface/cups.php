<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;
require_once("phpqrcode/qrlib.php");

//PARAMETROS
$date = new DateTime();
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

//tipo de pagamento utilizado na nota
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
//cabecalho da nota fiscal, informacoes sobre a empresa emissora
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
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text('CNPJ: '.$cnpj.' ');
    $printer -> setEmphasis(true);
    $printer->text($razao."\n");
    $printer -> setEmphasis(false);
    //$printer->text('IE:' . $ie); inscricao estadual
    //$printer->text('IM: '.$im); 
    $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    $printer -> setEmphasis(true);
    $printer->text("\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n");
    $printer -> setEmphasis(false);
    $printer->text(divisoria("DANFE NFC-e"));
}
//especificacoes dos itens da nota fiscal
function parteIII($nfce,$printer){
    $printer -> setEmphasis(true);
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer->text("Item Cod  |       Descrição     | Qtd | V.Unit | V.Total");
    $printer -> setEmphasis(false);
    //obter dados dos itens da NFCe
     $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        $printer->text("\n");
        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = (float)$value->prod->vUnCom;                //valor unitario
        $vProd = (float)$value->prod->vProd;   
        
        if(strlen($cProd)>=10){
            $aux = substr($cProd,0,7);
            $printer->text( $aux."\n");
            $cProd = substr($cProd,7);
        }
       
        $cProd = str_pad($cProd, 11,' ');
       
        $xProd = str_pad($xProd, 21,' ');
        $qCom = str_pad($qCom, 7,' ',STR_PAD_BOTH);
        $vUnCom = str_pad($vUnCom, 9,' ',STR_PAD_BOTH);
        $vProd = str_pad($vProd, 9,' ',STR_PAD_BOTH);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        $printer->text($cProd. $xProd . $qCom . $vUnCom . $vProd);
    }
    $printer -> setFont(); // Reset
}
//relação dos tributos emcima dos produtos
function parteIV($nfce,$printer){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    $printer->text("\n");
    $printer -> setEmphasis(true);
    $printer->text('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    $printer -> setEmphasis(false);
    $printer->text("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
}
//forma utilizada para acerto da nota fiscal
function parteV($nfce,$printer){
    
    $vNF = (float) $nfce->infNFe->total->ICMSTot->vNF;
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer->text('VALOR TOTAL R$ ' . $vNF);
    $printer->text("\n");
    $printer ->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text('FORMA PAGAMENTO');
    $printer ->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->text('VALOR PAGO');
    $printer -> setFont();
    $pag = $nfce->infNFe->pag->detPag;
    //$tot = $pag->count();
    foreach ($pag as $key) {
        //echo tipoPag((string)$key->tPag);
        $forma_pagamento = tipoPag((string)$key->tPag);
        $printer ->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n" . str_pad($forma_pagamento,17,STR_PAD_BOTH) . "- > " . $key->vPag);
    }
    $printer ->setJustification();
    $printer->text("\n------------------------------\n");
}
//informações para consulta da nota fiscal no site da receita
function parteVII($nfce,$printer,$aURI){
    $printer->text("\n");
    $tpAmb = (int) $nfce->infNFe->ide->tpAmb;
    if ($tpAmb == 2) {
        $printer ->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO-\nSEM VALOR FISCAL");
        $printer ->setJustification();
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

    $printer->text('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi);
    $printer -> setEmphasis(true);
    $printer->text("\n");
    $printer->text(divisoria("Via Consumidor"));

    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);

    $printer->text("Consulte pela chave de acesso em: \n");
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    $printer->text($uri);
    $printer->text("\n");
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text('CHAVE DE ACESSO');
    $printer->text("\n");
    $printer->text($chave);
    $printer ->setJustification();

    $printer -> setFont();
    $printer -> setEmphasis(false);
    $printer->text("\n");
}
function parteVIII($nfce,$printer){
    
    $printer -> text("\n");
    $printer -> setFont(Printer::FONT_B);
    $dest = $nfce->infNFe->dest;
    if (empty($dest)) {
        $printer->text('CONSUMIDOR NÃO IDENTIFICADO');
    }
    $xNome = (string) $nfce->infNFe->dest->xNome;
    $printer->text($xNome);
    $printer -> text("\n");
    $cnpj = (string) $nfce->infNFe->dest->CNPJ;
    $cpf = (string) $nfce->infNFe->dest->CPF;
    $idEstrangeiro = (string) $nfce->infNFe->dest->idEstrangeiro;
    if (!empty($cnpj)) {
        $printer->text('CNPJ ' . $cnpj);
    }
    if (!empty($cpf)) {
        $printer->text('CPF ' . $cpf);
    }
    if (!empty($idEstrangeiro)) {
        $printer->text('Extrangeiro ' . $idEstrangeiro);
    }
    $xLgr = (string) $nfce->infNFe->dest->enderDest->xLgr;
    $nro = (string) $nfce->infNFe->dest->enderDest->nro;
    $xCpl = (string) $nfce->infNFe->dest->enderDest->xCpl;
    $xBairro = (string) $nfce->infNFe->dest->enderDest->xBairro;
    $xMun = (string) $nfce->infNFe->dest->enderDest->xMun;
    $uf = (string) $nfce->infNFe->dest->enderDest->UF;
    $cep = (string) $nfce->infNFe->dest->enderDest->CEP;
    $printer->text($xLgr . '' . $nro . '' . $xCpl . '' . $xBairro . '' . $xMun . '' . $uf);
    //linha divisória ??
}
function divisoria($titulo){
    $titulo = str_pad($titulo, 42, '-', STR_PAD_BOTH);
    return $titulo;
}
//Carrega o arquivo XML e o retorna
function loadNFCe($nfcexml){
    $xml = $nfcexml;
    if (is_file($nfcexml)) {
        $xml = @file_get_contents($nfcexml);
    }
    if (empty($xml)) {
        throw new InvalidArgumentException('Não foi possivel ler o documento.');
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
//Seta string com enfase, deixando-o centralizado e em negrito
function title(Printer $printer, $text){
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> text("\n" . $text);
    $printer -> selectPrintMode(); // Reset
}
try {
    $connector = new CupsPrintConnector("bema2");
    $printer = new Printer($connector);
    //FIM PARAMETROS
    //$dirWatch = '../../../../../../../../../sircplus/dados/csag/nfce/f0100/ret';
    $dirWatch = '../pasta_teste';
    $inoInst = inotify_init();

    stream_set_blocking($inoInst, 0);

    $watch_id = inotify_add_watch($inoInst, $dirWatch, IN_ALL_EVENTS);


    while(true){
        // read events (
        // which is non blocking because of our use of stream_set_blocking
        $events = inotify_read($inoInst);
    
        //mask '2' evento que verifica se o arquivo esta sendo copiado para pasta
        if ($events[0]['mask'] === 2){
            $nome_nota = $events[0]['name'];
            if(substr($nome_nota,0,6) == 'retsai'){
                $printer = new Printer($connector);
                $printer -> initialize();
                $nfce = loadNFCe("../pasta_teste/".$events[0]['name']); 
                parteI($nfce,$printer,$aURI);
                echo "\n PART 1 ! \n";
                parteIII($nfce,$printer);
                echo "\n PART 2 ! \n";
                parteIV($nfce,$printer);
                echo "\n PART 3 ! \n";
                parteV($nfce,$printer);
                echo "\n PART 4 ! \n";
                parteVII($nfce,$printer,$aURI);
                echo "\n PART 5 ! \n";
                parteVIII($nfce,$printer);

                //QRCODE
                $qr = (string)$nfce->infNFeSupl->qrCode;
                echo("\nQRCODE: \n".$qr);
                if(!empty($qr)){
                    $tmpfname = tempnam(sys_get_temp_dir(), "temp");
                    QRcode::png($qr, $tmpfname);
                    $img = EscposImage::load($tmpfname);;
                    $printer->bitImage($img);
                    unlink($tmpfname);    
                }
                //QRCODE
                $printer ->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("\n Emissão : " . date("d-m-Y H:i:s") );
                $printer ->setJustification(Printer::JUSTIFY_CENTER);
     
                $printer->cut();
                $printer->close();
            }
        }
    }

    $printer -> close();
    //stop watching our directory
    inotify_rm_watch($inoInst, $watch_id);

    //close our inotify instance
    fclose($inoInst);
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
