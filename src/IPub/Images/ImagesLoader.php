<?php
/**
 * ImagesLoader.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Images!
 * @subpackage	common
 * @since		5.0
 *
 * @date		09.02.15
 */

namespace IPub\Images;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Images;
use IPub\Images\Exceptions;
use IPub\Images\Storage;
use IPub\Images\Templating;

class ImagesLoader extends Nette\Object
{
	/**
	 * @var Storage\IStorage[]
	 */
	protected $imageStorage = [];

	/**
	 * @param $arguments
	 *
	 * @return string
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function request($arguments)
	{
		if (!isset($arguments["storage"]) || $arguments["storage"] === NULL) {
			throw new Exceptions\InvalidArgumentException("Please provide image storage name.");
		}

		if (!isset($arguments["filename"]) || $arguments["filename"] === NULL) {
			throw new Exceptions\InvalidArgumentException("Please provide filename.");
		}

		$storage = $arguments["storage"];
		unset($arguments["storage"]);

		$namespace = $arguments["namespace"];
		unset($arguments["namespace"]);

		$filename = $arguments["filename"];
		unset($arguments["filename"]);

		$size = $arguments["size"];
		unset($arguments["size"]);

		$algorithm = $arguments["algorithm"];
		unset($arguments["algorithm"]);

		if (empty($filename)) {
			return "#";
		}

		// Parse size
		if (empty($size) || $size === NULL) {
			$size = 'original';

		} else if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode("x", $size);

			if ((int) $height > 0) {
				$size = (int) $width . 'x' . (int) $height;

			} else {
				$size = (int) $width;
			}

		} else {
			$size = (int) $size;
		}

		// Parse algorithm
		if (empty($algorithm) || $algorithm === NULL) {
			$algorithm = NULL;

		} else if ($algorithm == NULL) {
			$algorithm = Utils\Image::FIT;

		} else if (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm))
			{
				case "fit":
					$algorithm = Utils\Image::FIT;
					break;

				case "fill":
					$algorithm = Utils\Image::FILL;
					break;

				case "exact":
					$algorithm = Utils\Image::EXACT;
					break;

				case "shrink_only":
				case "shrinkonly":
				case "shrink-only":
					$algorithm = Utils\Image::SHRINK_ONLY;
					break;

				case "stretch":
					$algorithm = Utils\Image::STRETCH;
					break;

				default:
					$algorithm = NULL;
			}

		} else {
			$algorithm = NULL;
		}

		return $this->getStorage($storage)->setNamespace($namespace)->request($filename, $size, $algorithm);
	}

	/**
	 * @param string $storage
	 *
	 * @return Storage\IStorage
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function getStorage($storage)
	{
		if (isset($this->imageStorage[$storage])) {
			return $this->imageStorage[$storage];
		}

		throw new Exceptions\InvalidArgumentException("Storage '$storage' is not registered.");
	}

	/**
	 * @param Storage\IStorage $storage
	 *
	 * @return $this
	 */
	public function registerStorage(Storage\IStorage $storage)
	{
		$this->imageStorage[(string) $storage] = $storage;

		return $this;
	}

	/**
	 * @return Templating\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new Templating\Helpers($this);
	}
}