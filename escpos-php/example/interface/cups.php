<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;

require_once("phpqrcode/qrlib.php");
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
$connector = new CupsPrintConnector("bema2");
$printer = new Printer($connector);

$nfce = loadNFCe('teste_nota.xml');
$align = array(
    'left' => $printer->setJustification(Printer::JUSTIFY_LEFT),
    'mid' => $printer->setJustification(Printer::JUSTIFY_CENTER),
    'right' => $printer->setJustification(Printer::JUSTIFY_RIGHT),
    'reset' => $printer->setJustification()
);

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
function parteI($nfce,$aURI,$printer,$align){
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
    $align['left'];
    $printer->text('CNPJ: '.$cnpj.' ');
    $printer -> setEmphasis(true);
    $printer->text($razao."\n");
    $printer -> setEmphasis(false);
    //$printer->text('IE:' . $ie); inscricao estadual
    //$printer->text('IM: '.$im); 
    $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    $align['right'];
    $printer -> setEmphasis(true);
    $printer->text("\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n");
    $printer -> setEmphasis(false);
    $align['reset'];
}
function parteIII($nfce,$printer,$align){
    $printer -> setEmphasis(true);
    $align['mid'];
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer->text("Item Cod  |       Descrição     | Qtd | V.Unit | V.Total");
    $printer -> setEmphasis(false);
    //obter dados dos itens da NFCe
    $align['reset'];
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
function parteIV($nfce,$printer,$align){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    $printer->text("\n");
    $align['left'];
    $printer -> setEmphasis(true);
    $printer->text('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    $printer -> setEmphasis(false);
    $printer->text("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
    $align['reset'];
}
function parteV($nfce,$printer,$align){
    
    $vNF = (float) $nfce->infNFe->total->ICMSTot->vNF;
    $align['left'];
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer->text('VALOR TOTAL R$ ' . $vNF);
    $printer->text("\n");
    $printer->text('FORMA PAGAMENTO          VALOR PAGO');
    $printer -> setFont();
    $pag = $nfce->infNFe->pag;
    $tot = $pag->count();
    for ($x=0; $x<=$tot-1; $x++) {
        $tPag = tipoPag($pag[0]->tPag);
        $vPag = (float) $pag[0]->vPag;
        $printer->text($tPag . '                  R$ '. $vPag);
    }
    $align['reset'];
   
}
function parteVII($nfce,$printer,$align,$aURI){
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
    $align['left'];
    $printer->text('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi . ' via  Consumidor');
    $align['reset'];
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $align['mid'];
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
    $align['mid'];
    $printer -> setFont();
}
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
function title(Printer $printer, $text){
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> text("\n" . $text);
    $printer -> selectPrintMode(); // Reset
}
try {
 
    parteI($nfce,$aURI,$printer,$align);
    parteIII($nfce,$printer,$align);
    parteIV($nfce,$printer,$align);
    parteV($nfce,$printer,$align);
    parteVII($nfce,$printer,$align,$aURI);

    //QRCODE
    $qr = (string)$nfce->infNFeSupl->qrCode;
    $printer->text($qr);
    /*
    
    $tmpfname = tempnam(sys_get_temp_dir(), "temp");
    QRcode::png($qr, $tmpfname);
    $img = EscposImage::load($tmpfname);;
    $printer->bitImage($img);
    unlink($tmpfname);
    //QRCODE
    */
    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $tux = EscposImage::load("frame.png", false);
    $printer->setJustification();
    $printer -> bitImage($tux);
    $printer->text("Emissão : " . date("d-m-Y H:i:s") );
    $align['reset'];
 
    $printer -> cut();


    $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
