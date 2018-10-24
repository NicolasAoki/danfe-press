<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;


        /**
     * Parte I - Emitente
     * Dados do emitente
     * Campo Obrigatório
     */
    echo "teste";
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
        $printer->text('C');
        $printer->text($razao);
        $printer->text('CNPJ: '.$cnpj.'     '.'IE: ' . $ie);
        $printer->text('IM: '.$im);
        $printer->text('L');
        $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);

       
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
    
    try {
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
        $connector = new CupsPrintConnector("EPSON_TM-T20");
        $printer = new Printer($connector);
        $nfce = loadNFCe('../resources/teste_nota.xml');
        echo $nfce;
        parteI($nfce,$aURI);
        $printer -> cut();
        $printer -> close();
    } catch (Exception $e) {
        echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
    }


    
        /* Print a "Hello world" receipt"
        $printer = new Printer($connector);
        $printer -> text("Hello World!\n");
        $printer -> cut();
        
         Close printer */