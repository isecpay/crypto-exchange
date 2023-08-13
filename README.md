# isecpay/crypto-exchange
iSecPay Crypto Exchange API PHP SDK

---

### This library requires min. PHP 8.0

---

### You need an API Key. You can get one by contacting [ce-api@isecpay.co](mailto:ce-api@isecpay.co)

---

# Installation

``
composer require isecpay/crypto-exchange
``

# Examples
### Get number

```
// require_once './vendor/autoload.php';

// Replace 'YOUR_API_KEY' with your actual API key from iSecPay API.
$apiKey = 'YOUR_API_KEY';

try {
	$api = new PHPCore\CryptoExchange\Api($apiKey); // $api$key is optional if $_ENV['ISECPAY_CRYPTO_EXCHANGE_API_KEY'] is set

} catch (Exception $e) {
	echo "Error: " . $e->getMessage() . "\n";
}
```

---

## License
This project is licensed under the MIT License.
