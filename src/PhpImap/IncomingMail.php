<?php namespace PhpImap;

/**
 * @see https://github.com/barbushin/php-imap
 * @author Barbushin Sergey http://linkedin.com/in/barbushin
 */
class IncomingMail {

	public $id;
	public $date;
	public $subject;

	public $fromName;
	public $fromAddress;

	public $to = array();
	public $toString;
	public $cc = array();
	public $replyTo = array();

	public $messageId;

	public $textPlain;
	public $textHtml;
	public $mailStructure;

	/** @var IncomingMailAttachment[] */
	protected $attachments = array();

	public function addAttachment(IncomingMailAttachment $attachment) {
		$this->attachments[$attachment->id] = $attachment;
	}

	/**
	 * @return IncomingMailAttachment[]
	 */
	public function getAttachments() {
		return $this->attachments;
	}

	/**
	 * Get array of internal HTML links placeholders
	 * @return array attachmentId => link placeholder
	 */
	public function getInternalLinksPlaceholders() {
		return preg_match_all('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', $this->textHtml, $matches) ? array_combine($matches[2], $matches[1]) : array();

	}

	public function replaceInternalLinks($baseUri) {
		$baseUri = rtrim($baseUri, '\\/') . '/';
		$fetchedHtml = $this->textHtml;
		foreach($this->getInternalLinksPlaceholders() as $attachmentId => $placeholder) {
			if(isset($this->attachments[$attachmentId])) {
				$fetchedHtml = str_replace($placeholder, $baseUri . basename($this->attachments[$attachmentId]->filePath), $fetchedHtml);
			}
		}
		return $fetchedHtml;
	}
}

class IncomingMailAttachment {

	public $id;
	public $name;
	public $filePath;
	public $subtype;
	public $disposition;
	public $email_id;
	public $data;
	public $partStructure;

	public function save($dir, $prefix = '')
	{
		$replace = array(
			'/\s/' => '_',
			'/[^0-9a-zа-яіїє_\.]/iu' => '',
			'/_+/' => '_',
			'/(^_)|(_$)/' => '',
		);

		$fileSysName = $prefix . preg_replace('~[\\\\/]~', '', $this->email_id . '_' . $this->id . '_' . preg_replace(array_keys($replace), $replace, $this->name));

		$this->filePath = $dir . DIRECTORY_SEPARATOR  .$fileSysName;

		file_put_contents($this->filePath, $this->data);
	}
}
