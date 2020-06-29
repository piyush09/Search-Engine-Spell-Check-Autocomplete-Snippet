<?php
ini_set('memory_limit','1024M');

include 'SpellCorrector.php';
include 'simple_html_dom.php';
header('Content-Type: text/html; charset=utf-8');
$div=false;
$correct = "";
$correct1="";
$output = "";
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
if ($query)
{
  $choice = isset($_REQUEST['sort'])? $_REQUEST['sort'] : "default";

  require_once('solr-php-client/Apache/Solr/Service.php');

  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/homework/');
  if( ! $solr->ping()) { 
            echo 'Solr service is not available'; 
  } 
  else{
     
  }
  
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  try
  {
    if($choice == "default")
      $additionalParameters=array('sort' => '',"q.op" => 'AND');
    else{
      $additionalParameters=array('sort' => 'pageRankFile desc',"q.op" => 'AND');
    }
    $word = explode(" ",$query);
    $spell = $word[sizeof($word)-1];
    for($i=0;$i<sizeOf($word);$i++){
      ini_set('memory_limit',-1);
      ini_set('max_execution_time', 300);
      $che = SpellCorrector::correct($word[$i]);
      if($correct!="")
        $correct = $correct."+".trim($che);
      else{
        $correct = trim($che);
      }
        $correct1 = $correct1." ".trim($che);
    }
    $correct1 = str_replace("+"," ",$correct);
    $div=false;
    if(strtolower($query)==strtolower($correct1)){
      $results = $solr->search($query, 0, $limit, $additionalParameters);
      // echo "Hello world!<br>";
      // echo "Query is <br>" .$query;
    }
    else {
      $div =true;
      $results = $solr->search($query, 0, $limit, $additionalParameters);
      $link = "http://localhost/search_enhanced.php?q=$correct&sort=$choice";
      $output = "Did you mean: <a href='$link'>$correct1</a>";
    }

  }
  catch (Exception $e)
  {

    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}
?>
<html>
  <head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <title>PHP Solr Client Example</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <style>

	body {
		background-color: #ADD8E6;
	}

	.searchDiv{
		text-align: center;
	}
	.resultDiv{
		padding-bottom: 20px;
		padding-left: 15px;
	}
	.ResultLineDiv{
		padding-left: 10px;
		padding-bottom: 20px;
	}
	th{
		padding-right: 10px;
	}

	a {
		text-decoration: none;
	}

	a:hover {
		text-decoration: underline;
	}

	.table-result {
		width: 1100px;
		margin:auto;
	}


	
</style>
  </head>
  <body>

<div class= "searchDiv">
    <form  accept-charset="utf-8" method="get">
      <label for="q"><h1>Search Engine:</h1></label><br/>
      <div class = "formDiv">
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/><br/><br/> 
      
      <br>
      <input type="radio" name="sort" value="pagerank" <?php if(isset($_REQUEST['sort']) && $choice == "pagerank") { echo 'checked="checked"';} ?>>PageRank Google
      <input type="radio" name="sort" value="default" <?php if(isset($_REQUEST['sort']) && $choice == "default") { echo 'checked="checked"';} ?>>Solr's Lucene Search <br/><br/> 
      <input type="submit" class="btn-primary" />
    </form>
    </div>
    </div>

    <script>
   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/homework/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;
     var tags = [];
     $("#q").autocomplete({
       source : function(request, response) {
         var correct="",before="";
         var query = $("#q").val().toLowerCase();
         var character_count = query.length - (query.match(/ /g) || []).length;
         var space =  query.lastIndexOf(' ');
         if(query.length-1>space && space!=-1){
          correct=query.substr(space+1);
          before = query.substr(0,space);
        }
        else{
          correct=query.substr(0); 
        }
        var URL = URL_PREFIX + correct+ URL_SUFFIX;
        $.ajax({
         url : URL,
         success : function(data) {
          var js =data.suggest.suggest;
          var docs = JSON.stringify(js);
          var jsonData = JSON.parse(docs);
          var result =jsonData[correct].suggestions;
          var j=0;
          var stem =[];
          for(var i=0;i<5 && j<result.length;i++,j++){
            if(result[j].term==correct)
            {
              i--;
              continue;
            }
            for(var k=0;k<i && i>0;k++){
              if(tags[k].indexOf(result[j].term) >=0){
                i--;
                continue;
              }
            }
            if(result[j].term.indexOf('.')>=0 || result[j].term.indexOf('_')>=0)
            {
              i--;
              continue;
            }
            var s =(result[j].term);
            if(stem.length == 5)
              break;
            if(stem.indexOf(s) == -1)
            {
              stem.push(s);
              if(before==""){
                tags[i]=s;
              }
              else
              {
                tags[i] = before+" ";
                tags[i]+=s;
              }
            }
          }
          console.log(tags);
          response(tags);
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
 </script>
<?php
if ($div){
  echo $output;
}
$csvArray =  array_map('str_getcsv', file('UrlToHtml_NBCNews.csv'));
$count =0;
$pre="";

// results dislaying
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
 
  foreach ($results->response->docs as $doc)
  {
    
    $id = $doc->id;
    $or_id = $id;
    $id = str_replace("/home/piyush/Desktop/NBC_News/crawl/","",$id);
    $descp = $doc->og_description;
    $title = $doc->title;
    foreach ($csvArray as $key ) {
      # code...
      if ($id == $key[0]){
        $link = $key[1];
        break;
      }
    }

    //search content
    $searchterm = $_GET["q"];
    $ar = explode(" ", $searchterm);
    $html_to_text_files_dir = "/home/piyush/Desktop/NBC_News/crawl/";///////
    $filename = $html_to_text_files_dir . $id;
    $html = file_get_contents($filename);
    $sentences = explode(".", $html);
    $words = explode(" ", $query);
    $snippet = "";
    $text = "/";
    $start_delim="(?=.*?\b";
    $end_delim="\b)";
    foreach($words as $item){
      $text=$text.$start_delim.$item.$end_delim;
    }
    $text=$text."^.*$/i";
    foreach($sentences as $sentence){
      $sentence=strip_tags($sentence);
      if (preg_match($text, $sentence)>0){
        if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
          continue;
        }
        else{
          $snippet = $snippet.$sentence;
          if(strlen($snippet)>160) 
            break;
        }
      }
    }

    $words = preg_split('/\s+/', $query);
  foreach($words as $item)
	$snippet = str_ireplace($item, "<strong>".$item."</strong>",$snippet);
    if($snippet == ""){
      $snippet = $descp;
    }
  
?>
      <li>
        <table style="border: 0px solid black; width=500px text-align: left">
          <tr>
            <qw><?php echo "<a href = '{$link}' STYLE='text-decoration:none'><font size='4px'><b>".$title."</b></font></a>" ?></qw>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("URL link", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo "<a href = '{$link}' STYLE='text-decoration:none'><st>".$link."</st></a>" ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("URL id", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("URL_description", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("snippet", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
            if($snippet == "N/A"){
              echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8');
            }else{
              echo "...".$snippet."...";
            }
            ?></td>
          </tr>
          <tr>
            <th><br></th>
            <td><br></td>
          </tr>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
