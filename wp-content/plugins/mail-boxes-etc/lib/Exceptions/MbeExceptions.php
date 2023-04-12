<?php

namespace MbeExceptions {

	use Throwable;

	class ValidationException extends \Exception {
		public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
			parent::__construct( $message, $code, $previous );
		}
	}

	class FileUploadException extends \Exception {
		public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
			parent::__construct( $message, $code, $previous );
		}
	}

	class FileSystemException extends \Exception {
		public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
			parent::__construct( $message, $code, $previous );
		}
	}

	class HttpRequestException extends \Exception {
		public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
			parent::__construct( $message, $code, $previous );
		}
	}
}