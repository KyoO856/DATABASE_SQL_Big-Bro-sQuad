<?php 

    class UserRegister {
        private $conn;
        private $table_name = "user";

        public $firstName;
        public $lastName;
        public $email;
        public $password;
        public $confirm_password;
        public $birthday;
        public $phone;
        public $address;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function setFirstName($firstName) {
            $this->firstName = $firstName;
        }

        public function setLastName($lastName) {
            $this->lastName = $lastName;
        }

        public function setEmail($email) {
            $this->email = $email;
        }

        public function setPassword($password) {
            $this->password = $password;
        }

        public function setConfirmPassword($confirm_password) {
            $this->confirm_password = $confirm_password;
        }

        public function setBirthday($birthday) {
            $this->birthday = $birthday;
        }

        public function setPhone($phone) {
            $this->phone = $phone;
        }

        public function setAddress($address) {
            $this->address = $address;
        }

        public function validatePassword() {
            if ($this->password !== $this->confirm_password) {
                return false; // Passwords do not match
            } 

            return true; // Passwords match
        }

        public function checkPasswordLength() {
            if (strlen($this->password) < 6) {
                return false;
            }
            return true;
        }

        public function validateUserInput() {
            if (!$this->checkPasswordLength() || !$this->validatePassword() || $this->checkEmail()) {
                return false;
            } 
            return true;
        }

        public function createUser() {

            // Validate User Input
            if (!$this->validateUserInput()) {
                return false;
            }

            $query = "INSERT INTO {$this->table_name}(name, email, password, birthday, phone, address) 
                      VALUES(:name, :email, :password, :birthday, :phone, :address)";
            $stmt = $this->conn->prepare($query);

            $fullName = $this->firstName . ' ' . $this->lastName;

            // Hash the password
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

            $stmt->bindParam(":name", $fullName);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":birthday", $this->birthday);
            $stmt->bindParam(":phone", $this->phone);
            $stmt->bindParam(":address", $this->address);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function checkEmail() {
            $query = "SELECT id FROM {$this->table_name} WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $this->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return true; // Email exists
            } else {
                return false; // Email does not exists
            }
        }
    }

?>