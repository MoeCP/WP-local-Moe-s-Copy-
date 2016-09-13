<?php
  Class CP_API{
    //Variables
    private $url = "https://api.copypress.com/";
    public $header = "Content-type: text/xml";
    public $apikey;
    public $apisig;

    function call_api($article_id){
    header("Content-type: text/xml");
    $articleArray = array(
      'sssdata'=> '<sssrequest><apikey>'.$this->apikey.'</apikey><apisignature>'.$this->apisig.'</apisignature><downloadarticle><articleid>'.$article_id.'</articleid></downloadarticle></sssrequest>'
     );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, $this->header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $articleArray);
    $response = curl_exec($ch);
    if(curl_errno($ch))  {
        print curl_error($ch);
    }else {
      //print_r($response);
      return $response;
      }
    }
}
