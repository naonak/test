<?php

interface ParsableFile extends IteratorAggregate
{
/**
 * Initialise l'instance. Si $file est fourni, l'instance tente de créer une ressource pour ce fichier
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
    public function __construct($file, $options);
/**
 * Ouvre une ressource pour ce fichier et initialise les options de l'instance (parsage, encodage)
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne true si tout est ok sinon false
 * @access public
 */
    public function open($file, $options);
/**
 * Parse le fichier à traiter et renvoie le résultat sous forme de tableau
 *
 * @return array le résultat sous forme de tableau
 * @access public
 */
    public function parse();
/**
 * Ferme la ressource et réinitialise les attributs de l'instance
 *
 * @return bool retourne true si tout est ok sinon false
 * @access public
 */
    public function close();
/**
 * Renvoie l'encodage du fichier si il a été ouvert sinon null
 *
 * @return string encodage du fichier si il a été ouvert sinon null
 * @access public
 */
    public function getEncoding();
/**
 * Renvoie les données contenues dans le fichier sous forme de tableau
 * Parse automatiquemen si cela n'a pas été fait
 *
 * @return array les données sous forme de tableau
 * @access public
 */
    public function getData();
}

class CsvFileParser implements ParsableFile
{
/**
 * Les données ligne par ligne du fichier parsé
 *
 * @var array
 * @access public
 */
	public $data = null;
/**
 * Le type d'encodage du fichier
 *
 * @var array
 * @access public
 */
    public $encoding = null;

/**
 * Le nom du fichier traité
 *
 * @var array
 * @access protected
 */
	protected $file = null;

/**
 * Les options passées à l'instance qui servent au parsage
 *
 * @var array
 * @access protected
 */
	protected $options = null;

/**
 * La ressource finfo du fichier
 *
 * @var array
 * @access protected
 */
	protected $ressource = null;
/**
 * Initialise l'instance. Si $file est fourni, l'instance tente de créer une ressource pour ce fichier
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function __construct($file = null, $options = array())
	{
		if (isset($file)) {
			return $this->open($file, $options);
		}
		return true;
	}

/**
 * Ouvre une ressource pour ce fichier et initialise les options de l'instance (parsage, encodage)
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function open($file, $options = array())
	{
		$finfo = new finfo(FILEINFO_MIME_ENCODING);
		if (!$finfo) {
		    trigger_error("Échec de l'ouverture de la base de données fileinfo", E_USER_WARNING);
		}
		$encoding = $finfo->file($file);
		$this->encoding = $encoding;
	
		$this->options = array_merge(array('headings' => false, 'delimiter' => ',', 'enclosure' => "\"", 'encoding' => $this->encoding), $options);
		$this->ressource = fopen($file, 'r');

		return true;
	}

/**
 * Parse le fichier à traiter et renvoie le résultat sous forme de tableau
 *
 * @return array le résultat sous forme de tableau
 * @access public
 */
	function parse()
	{
		if(!$this->ressource) {
			trigger_error("Lancer la méthode 'open' avant de lancer la méthode 'parse'", E_USER_ERROR);
		}

		extract($this->options);
		
		$this->data = array();
		
		$i = 0;
		$rows = array();

		while (($row = fgetcsv($this->ressource, 4096, $delimiter, $enclosure)) !== FALSE)
		{
			if ($headings) {
				if ($i === 0) {
					$headingTexts = $row;
					$i++;
					continue;
				}
				foreach ($row as $key => $value)
				{
					unset($row[$key]);
					$row[$headingTexts[$key]] = $value;
				}
			}
			$this->data[] = $row;
			$i++;
		}

		return $this->data;
	}

/**
 * Ferme la ressource et réinitialise les attributs de l'instance
 *
 * @return bool retourne true si tout est ok sinon false
 * @access public
 */
	function close()
	{
		if ($this->ressource) {
			fclose($this->ressource);
		}
		$this->file = null;
		$this->ressource = null;
		$this->data = null;

		return true;
	}
/**
 * Renvoie les données contenues dans le fichier sous forme de tableau
 * Parse automatiquemen si cela n'a pas été fait
 *
 * @return array les données sous forme de tableau
 * @access public
 */
	function getData()
	{
		if (!isset($this->data)) {
			$this->parse();
		}
		return $this->data;
	}
/**
 * Renvoie l'encodage du fichier si il a été ouvert sinon null
 *
 * @return string encodage du fichier si il a été ouvert sinon null
 * @access public
 */
	function getEncoding()
	{
		return $this->encoding;
	}

	function getIterator()
	{
		if (!isset($this->data)) {
			$this->parse();
		}
		$iterator = new ParseFileIterator($this->data);
		return $iterator;
	}

}

class XmlFileParser implements ParsableFile
{
/**
 * Les données ligne par ligne du fichier parsé
 *
 * @var array
 * @access public
 */
	public $data = null;
/**
 * Le type d'encodage du fichier
 *
 * @var array
 * @access public
 */
    public $encoding = null;

/**
 * Le nom du fichier traité
 *
 * @var array
 * @access protected
 */
	protected $file = null;

/**
 * Les options passées à l'instance qui servent au parsage
 *
 * @var array
 * @access protected
 */
	protected $options = null;

/**
 * La ressource finfo du fichier
 *
 * @var array
 * @access protected
 */
	protected $ressource = null;

/**
 * Initialise l'instance. Si $file est fourni, l'instance tente de créer une ressource pour ce fichier
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function __construct($file = null, $options = array())
	{
		if (isset($file)) {
			return $this->open($file, $options);
		}
		return true;
	}
/**
 * Ouvre une ressource pour ce fichier et initialise les options de l'instance (parsage, encodage)
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function open($file, $options = array()) {
		
		$finfo = new finfo(FILEINFO_MIME_ENCODING);
		if (!$finfo) {
		    trigger_error("Échec de l'ouverture de la base de données fileinfo", E_USER_WARNING);
		}
		$encoding = $finfo->file($file);
		$this->encoding = $encoding;

   		$this->ressource = new XMLReader();
  		$this->ressource->open($file, $encoding);
		$this->options = array_merge(array('complexParsing' => false, 'path' => null, 'tag' => null, 'encoding' => $this->encoding), $options);

		return true;
	}
/**
 * Parse le fichier à traiter et renvoie le résultat sous forme de tableau
 * 
 * Options :
 * 	path : le chemin qui cible les éléments à extraire ligne par ligne. exp: 'rss.channel.item'
 *  tag : seuls les l'éléments avec ce nom seront retournés (TODO: compatibilité avec 'complexParsing')
 * 	complexParsing : si à TRUE, les éléments seront représentés sous la forme : array( "tag" => "nom du tag", "value" => "valeur", "attributes" => array( "attr1" => "valeur" }
 *
 * @return array retourne le résultat sous forme de tableau
 * @access public
 */
	function parse() {
		if(!$this->ressource) {
			trigger_error("Lancer la méthode 'open' avant de lancer la méthode 'parse'", E_USER_ERROR);
		}
		$this->data = array();
		return $this->data = (array) $this->_parse();
	}
/**
 * Parse récursivement l'xml selon les options
 *
 * @return array retourne le résultat sous forme de tableau
 * @access protected
 */
	protected function _parse($_path = null)
	{
		extract($this->options);

		if (is_null($_path) && $path) {
			$_path = explode('.', $path);
		}
		if($_path) {
			$path = array_shift($_path);
		}
		$dest = false;
		if (is_array($_path) && count($_path) === 0) {
			$dest = true;
			$_path = null;
		}
		
		$tree = null;
	    while($this->ressource->read())
	    {
	    	if(isset($path) && $this->ressource->name === $path && $dest == false) {
     			return $this->_parse($_path);
	    	}
	    	if (isset($path) && $dest && $this->ressource->name !== $path) {
	            continue;
	        }
	        switch ($this->ressource->nodeType)
	        {
	            case XMLReader::END_ELEMENT: return $tree;
	            case XMLReader::ELEMENT:
	            	$mode = 0; // 0 : associatif,1 : indexé numériquement 2 : ne pas ajouter l'élément mais parcourir ses enfants
	        	    if(isset($tag) && $this->ressource->name !== $tag) {
	        	    	$mode = 2;
		    		}
	        	    if ((isset($path) && $dest && $this->ressource->name === $path) || (isset($tag) && $this->ressource->name === $tag)) {
			    		$mode = 1;
			    	}
	            	if ($complexParsing) {
		                $node = array('tag' => $this->ressource->name, 'value' => $this->ressource->isEmptyElement ? '' : $this->_parse($_path));
		                if($this->ressource->hasAttributes) {
		                    while($this->ressource->moveToNextAttribute())
		                    {
		                        $node['attributes'][$this->ressource->name] = $this->ressource->value;
		                    }
		                }
		                if($mode === 2) {
		                	if(is_array($node['value'])) {
		                		$tree = array_merge((array) $tree, array($node));
		                	}
		                	
		                	continue;
		                }
	                	$tree[] = $node;
		                continue;
	            	}
	            	if ($mode === 1) {
			    		$tree[] = $this->ressource->isEmptyElement ? '' : $this->_parse($_path);
			    	} else if($mode === 2) {
			    		$res = $this->ressource->isEmptyElement ? '' : $this->_parse($_path);
			    		if (is_array($res)) {
			    			$tree = array_merge((array) $tree, $res);
			    		}
			    	} else {
			    		$tree[$this->ressource->name] = $this->ressource->isEmptyElement ? '' : $this->_parse($_path);
			    	}
	            break;
	            case XMLReader::TEXT:
	            case XMLReader::CDATA:
	                $tree .= $this->ressource->value;
	        }
	    }
		return $tree;
	}

/**
 * Ferme la ressource et réinitialise les attributs de l'instance
 *
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function close()
	{
		if ($this->ressource) {
			$this->ressource->close(); 
		}
		$this->file = null;
		$this->ressource = null;
		$this->data = null;

		return true;
	}
/**
 * Renvoie les données contenues dans le fichier sous forme de tableau
 * Parse automatiquemen si cela n'a pas été fait
 *
 * @return array les données sous forme de tableau
 * @access public
 */
	function getData()
	{
		if (!isset($this->data)) {
			$this->parse();
		}
		return $this->data;
	}
/**
 * Renvoie l'encodage du fichier si il a été ouvert sinon null
 *
 * @return string encodage du fichier si il a été ouvert sinon null
 * @access public
 */
	function getEncoding()
	{
		return $this->encoding;
	}
	
	function getIterator()
	{
		if (!isset($this->data)) {
			$this->parse();
		}
		$iterator = new ParseFileIterator($this->data);
		return $iterator;
	}

}

class ParseFileIterator implements Iterator
{

	private $array = array() ;
	private $key ;
	private $current ;

	function __construct( $array )
	{
		$this->array = $array ;
	}

	function rewind()
	{
		reset($this->array);
		$this->next();
	}

	function valid()
	{
		return $this->key !== NULL;
	}

	function key()
	{
		return $this->key;
	}

	function current()
	{
		return $this->current;
	}

	function next()
	{
		list($key, $current) = each($this->array);
		$this->key = $key;
		$this->current = $current;
	}

}


class FileParser implements ParsableFile
{
/**
 * Un objet implémentant "ParsableFile"
 *
 * @var ParsableFile
 * @access protected
 */
	protected $Parser = null;
/**
 * L'extension du fichier traité
 *
 * @var string
 * @access public
 */
	public $extension = null;

	function __construct($file = null, $options = array())
	{
		if (isset($file)) {
			return $this->open($file, $options);
		}
		return true;
	}
/**
 * Par rapport à l'extension du fichier passé en paramètre, la méthode essaie de crée un instance adapté à l'extension avec une classe du type "[Extension]FileParser"
 * exemple : pour un .xml, la méthode va tester l'existence de la classe "XmlFileParser" et vérifier qu'elle implémente bien l'interface "ParsableFile"
 * Si la classe adaptée est trouvée, une ressource pour ce fichier est ouverte et les options sont initialisées
 *
 * @param string $file le nom du fichier à traiter
 * @param array $options paramètre optionnel qui spécifie les options de l'instance (parsage, encodage)
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function open($file, $options = array())
	{

		if (!file_exists($file)) {
			trigger_error(sprintf("Le fichier %s n'existe pas", htmlspecialchars($file)), E_USER_ERROR);
		}
		
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		
		if(!$extension) {
			trigger_error(sprintf("Impossible de lire l'extension du fichier %s", htmlspecialchars($file)), E_USER_ERROR);
		}
		$this->extension = strtolower($extension);
		
		$className = ucfirst($this->extension) . 'FileParser';
		
		if(!class_exists($className)) {
			trigger_error(sprintf("La class %s n'existe pas", htmlspecialchars($className)), E_USER_ERROR);
		}

		try {
			$instance = new $className;
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
		
		if ( !($instance instanceof ParsableFile) ) {
			trigger_error(sprintf("La class %s n'implémente pas l'interface 'ParsableFile'", htmlspecialchars($className)), E_USER_ERROR);
		}

		$this->Parser = $instance;

		return $this->Parser->open($file, $options);
	}
/**
 * Parse le fichier à traiter et renvoie le résultat sous forme de tableau
 *
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function parse()
	{
		if(!$this->Parser) {
			trigger_error("Lancer la méthode 'open' avant de lancer la méthode 'parse'", E_USER_ERROR);
		}
		return $this->Parser->parse();
	}
/**
 * Ferme la ressource et réinitialise les attributs de l'instance
 *
 * @return bool retourne TRUE si tout est ok sinon false
 * @access public
 */
	function close()
	{
		$this->extension = null;
		if($this->Parser) {
			$this->Parser->close();
			$this->Parser = null;
		}
		return true;
	}
/**
 * Renvoie les données contenues dans le fichier sous forme de tableau
 * Parse automatiquemen si cela n'a pas été fait
 * 
 * Peut être appelé statiquement en passant le nom du fichier
 * NB : ne respecte pas le standard 'strict', il faudrait une autre méthode de type 'static'
 *
 * @return array les données sous forme de tableau
 * @access public
 */
	function getData($file = null, $options = array())
	{
		if (!isset($this)) {
			if(!$file) {
				trigger_error("En mode static, cette méthode doit disposer d'un paramètre $file valide", E_USER_ERROR);
			}
			$_this = new FileParser($file, $options);
			$data = $_this->getData();
			$_this->close();
			return $data;
		}
		return $this->Parser->getData();
	}
/**
 * Renvoie l'encodage du fichier si il a été ouvert sinon null
 *
 * @return string encodage du fichier si il a été ouvert sinon null
 * @access public
 */
	function getEncoding()
	{
		return $this->Parser->getEncoding();
	}

	function getIterator()
	{
		$iterator = $this->Parser->getIterator();
		return $iterator;
	}

}

error_reporting(E_ALL /*| E_STRICT*/);

if (!defined('MAGIC')) {
    define('MAGIC', "/usr/share/misc/magic"); // pour linux
   // define('MAGIC', 'C:/wamp/php/extras/magic'); // pour windows avec Wamp
}

echo '<pre>';

/* Test CSV */

// Récupération des données de façon statique en une ligne
var_dump( FileParser::getData('famous.csv', array('headings' => true)) );

$csv = new FileParser();
$csv->open('famous.csv', array('headings' => true));

echo 'Le fichier CSV est encodé en ', $csv->getEncoding(), "\n";

// Test iterator
foreach( $csv as $row ) {
     var_dump($row);
}

$csv->close();

/* Test XML */

$xml = new FileParser('news.xml', array('path' => 'rss.channel.item'));

echo 'Le fichier XML est encodé en ', $xml->getEncoding(), "\n";

var_dump($xml->getData());

$xml = new FileParser('news.xml', array('tag' => 'title'));
var_dump($xml->getData());

$xml = new FileParser('news.xml', array('complexParsing' => true));
var_dump($xml->getData());

$xml->close();

echo '</pre>';


?>