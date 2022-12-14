<?php

    require_once(__DIR__ . '/../Base.php');

    class TokenException extends BaseObjectException {
        public function __construct($message, $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    abstract class TokenBase extends Base {

        protected $expiryTime;
        protected $token;

        protected function __construct($row = null) {
            parent::__construct($row);

            $expiryTime = parent::getValueFromRow($row, 'expiryTime', null, FieldTypeEnum::BIGINT);
            $token = parent::getValueFromRow($row, 'token');

            $this->expiryTime = new Field('expiryTime', 'BIGINT', $expiryTime, 'NOT NULL');
            $this->token = new Field('token', 'VARCHAR(100)', $token, 'NOT NULL');
        }

        protected abstract function deleteToken();
        protected abstract function setExpiryTime();

        public function generateToken() {
            $bytes = random_bytes(50);
            $token = bin2hex($bytes);
            if (strlen($token) > 100) $token = substr($token, 0, 100);
            $this->token->set($token);
            $this->setExpiryTime();
            $this->changed = true;
        }

        public function isTokenExpired() {
            $time = time();
            if ($time > $this->expiryTime->get()) {
                $this->deleteToken();
                return true;
            }
            return false;
        }

        public function __set($property, $value) {
            switch($property) {
                case 'expiryTime';
                    throw new TokenException('Setting expiryTime not permitted');
                    break;
                case 'token';
                    throw new TokenException('Setting Token not permitted');
                    break;
                default:
                    parent::__set($property, $value);
                    break;
            }
        }

        public function __get($property) {
            return $this->$property->get();
        }

        /**
         * @throws TokenException always
         */
        public function destroy() {
            throw new TokenException("Unable to manually destroy token");
        }
    }


?>