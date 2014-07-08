argument-configurator
=====================

Simple class to compensate for the lack of keyworded arguments in PHP.  This is useful for functions that take many arguments, where many are optional and have default values.

Install
=======


Example
-------

In this example, we have an Order class with a toHTML method that renders the order into HTML.

This method has a large number of configuration options.  For example, show item action buttons on the HTML output of every item in the order, what headers to show, who is requestion the output, etc.

```

use ArgumentValidator\ArgumentValidator;

class Order extends BaseOrder {

	protected $toArrayConfig;

	public function __construct() {
		$this->toArrayConfig = new ArgumentValidator();
		$this->toArrayConfig->addOpt('auth', 'obj:\Mayofest\Auth', false, \Mayofest\Auth::getInstance());
		$this->toArrayConfig->addOpt('itemActions', 'bool', false, false); // Actions for items, delete, discount, refund
		$this->toArrayConfig->addOpt('adminView', 'bool', false, false); // Top toggle button, FB and Email icon
		$this->toArrayConfig->addOpt('adminOrderActions', 'bool', false, false); // Button buttons, recalc, del, cancel
		$this->toArrayConfig->addOpt('userOrderActions', 'bool', false, false); // User buttons, confirm, cancel
		$this->toArrayConfig->addOpt('activity', 'bool', false, true); // Show order acitivty
		$this->toArrayConfig->addOpt('itemTax', 'bool', false, false); // Show tax on individual items
		$this->toArrayConfig->addOpt('omit_discounts', 'bool', false, false); // Don't show discounts
		$this->toArrayConfig->addOpt('omit_donations', 'bool', false, false); // Don't show donations
		$this->toArrayConfig->addOpt('groupSimilar', 'bool', false, false); // Group by class & options, sum quantity and price
		$this->toArrayConfig->addOpt('labelDonations', 'bool', false, false); // Change "Donation" to "5$ donation"
		$this->toArrayConfig->addOpt('moneyFmt', 'text', false, MONEY_FMT);
		$this->toArrayConfig->addOpt('shortTitle', 'bool', false, false); // Change "Donation" to "5$ donation"
		$this->toArrayConfig->addOpt('statusInTitle', 'bool', false, false); // Put the status in the title
	}

	/**
	* Converts the order object into an array that is used by output serializers (HTML, LaTeX, Text, etc)
	* @param ArgumentValidator $config Config output (see the constructor for an up to date list if inputs)
	*
	* @returns string All the info to be rendered
	*/
	public function __toArray($config = array()) {
		// Let the exception rise
		$config = $this->toArrayConfig->validate($config, $str);

		// Our object (array)
		// Most options removed for example
		$obj = array('headers'=>array(),
			'orderId' => $oid, // Order title
			'userName' => $user->getName(),
			'userNameAndId' => $user->getNameAndId(),
			'userShortName' => sprintf('%s. %s', substr($user->getFirstName(), 0, 1), $user->getLastName()),
			'title' => NULL, // Order title
			'itemTax' => $config->getConf('itemTax'),
			'items' => array(), // button "objects"
			// ...
		);

		if ($config->getConf('adminView')) {
			$obj['user_fb_id'] = $this->getUserRelatedByUserId()->getFbId();
			$obj['user_email'] = $this->getUserRelatedByUserId()->getEmail();
			$obj['user_contact_html'] = $this->getUserRelatedByUserId()->getNameAndContact();
		}

		// Headers
		$obj['headers'] = array('item'=>'Item', 'details' => 'Details', 'quantity' => 'Quantity');
		if ($config->getConf('itemTax'))
			$obj['headers']['itemTax'] = 'Tax';
		if ($config->getConf('itemActions'))
			$obj['headers']['itemActions'] = 'Actions';

		// ...

		return $obj;
	}
}

```
