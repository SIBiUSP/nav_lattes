<?php 

include('inc/config.php');             
include('inc/functions.php');

$client_harvester = new \Phpoaipmh\Client(''.$_GET["url"].'');
$myEndpoint = new \Phpoaipmh\Endpoint($client_harvester);

// Result will be a SimpleXMLElement object
$result = $myEndpoint->identify();
var_dump($result);

// Results will be iterator of SimpleXMLElement objects
$results = $myEndpoint->listMetadataFormats();
foreach($results as $item) {
    var_dump($item);
}

if ($_GET["metadata_format"] == "nlm") {
    



    // Recs will be an iterator of SimpleXMLElement objects
    $recs = $myEndpoint->listRecords('nlm');

    // The iterator will continue retrieving items across multiple HTTP requests.
    // You can keep running this loop through the *entire* collection you
    // are harvesting.  All OAI-PMH and HTTP pagination logic is hidden neatly
    // behind the iterator API.
    foreach($recs as $rec) {

        if ($rec->{'header'}->attributes()->{'status'} != "deleted"){

    //    print_r($rec->{'header'});
    //    echo '<br/><br/><br/>';
    //    print_r($rec->{'metadata'});
    //    
    //    echo '<br/><br/><br/>';
    //    echo '<br/><br/><br/>';

        // Palavras-chave 
        foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'} as $palavra_chave) {
            $palavra_chave_array[] = $palavra_chave;        
        }


        $container_title = '
            "periodico":{
                "titulo_do_periodico":"'.str_replace('"','',$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'}).'",
                "nome_da_editora":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'publisher'}->{'publisher-name'}.'",
                "issn":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'}.'",
                "volume":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'}.'",
                "fasciculo":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue'}.'",
                "pagina_inicial":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'}.'",
                "serie":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'}.'"
            },
        ';

            foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'contrib-group'}->{'contrib'}  as $autores) {

                if ($autores->attributes()->{'contrib-type'} == "author"){
                    //print_r($autores);
                    //echo '<br/><br/>';                


                    $autores_base_array = [];

                    $autores_base_array[] = '"nome_completo_do_autor":"'.$autores->{'name'}->{'given-names'}.' '.$autores->{'name'}->{'surname'}.'"';
                    $autores_base_array[] = '"nome_para_citacao":"'.$autores->{'name'}->{'surname'}.', '.$autores->{'name'}->{'given-names'}.'"';

                    if(isset($autores->{'aff'})) {
                        $autores_base_array[] = '"afiliacao":"'.$autores->{'aff'}.'"';
                    }              
                    if(isset($autores->{'uri'})) {
                        $autores_base_array[] = '"nro_id_cnpq":"'.$autores->{'uri'}.'"';
                    }  

                    $autores_array[] = '{ 
                        '.implode(",",$autores_base_array).'
                    }';
                    unset($autores_base_array);
                }
            }    

        $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');

        $query_harvester = 
            '{
                "doc":{
                    "source":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'}.'",  
                    "harvester_id": "'.$rec->{'header'}->{'identifier'}.'",
                    "tag": ["'.$_GET["tag"].'"],
                    "tipo":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-categories'}->{'subj-group'}->{'subject'}.'",
                    "titulo": "'.str_replace('"','',$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'title-group'}->{'article-title'}).'",
                    "ano": "'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'pub-date'}[0]->{'year'}.'",
                    "doi":"'.$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1].'",
                    '.$container_title.'
                    "resumo":"'.str_replace('"','',$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'abstract'}->{'p'}).'",
                    "palavras_chave":["'.implode('","',$palavra_chave_array).'"],	
                    "autores":['.implode(',',$autores_array).']

                },
                "doc_as_upsert" : true
            }';  

        //print_r($query_harvester);

        $resultado = store_record($client,$sha256,$query_harvester);
        print_r($resultado);  


        //Limpar variáveis
        unset($palavras_chave_array);
        unset($autores_array); 

        }
    }
} else {
    $recs = $myEndpoint->listRecords('oai_dc');
    foreach($recs as $rec) {
        print_r($rec);
    }
    
}


?>