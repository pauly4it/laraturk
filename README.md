# LaraTurk

Provides a Laravel 5 package to communicate with the Amazon Mechanical Turk API.

## Resources

Mechanical Turk Production Websites:
- Mechanical Turk Site: https://www.mturk.com/mturk/welcome
- Requestor Site: https://requester.mturk.com/

Mechanical Turk Sandbox Websites:
- Requestor Sandbox: https://requestersandbox.mturk.com/
- Worker Sandbox: https://workersandbox.mturk.com/mturk/welcome

Amazon Mechanical Turk Documentation:
- Requestor Documentation: http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMechanicalTurkRequester/Welcome.html
- API Documentation: http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/Welcome.html 

## Installation

Install by adding laraturk to your composer.json file:

    require : {
        "pauly4it/laraturk": "dev-master"
    }

or with a composer command:

    composer require "pauly4it/laraturk": "dev-master"

After installation, add the provider to your config/app.php providers:

    'Pauly4it\LaraTurk\LaraTurkServiceProvider',

and the facade to config/app.php aliases:

	'LaraTurk' => 'Pauly4it\LaraTurk\Facades\LaraTurk',

## Configuring LaraTurk

First publish the config file:

	php artisan vendor:publish

This will create a `laraturk.php` config file. There you can define default values of parameters used in all functions.

If you will only be creating one type of HIT, you should specify all the default values in the config file.

You will also need to set two environment variables which the laraturk.php config file uses: `AWS_ROOT_ACCESS_KEY_ID` and `AWS_ROOT_SECRET_ACCESS_KEY`. If these are not set and you try to use LaraTurk, LaraTurk will throw a `LaraTurkException`.

## Usage

For all functions (except `getAccountBalance`), you will pass the function an array of paramaters. If you have defaults set in the config file, then the parameters you pass will override the defaults (for the keys you assign). For most of the implemented functions, refer to AWS documentation for required and optional parameters and the format of those parameters. A few parameters require different formats in LaraTurk compared to official documentation. Please see the "Special Parameter Formats" section below for those.

You must signup on the Mechanical Turk Requester site using your AWS root account email. If you plan on using the Mechanical Turk sandbox (highly recommended), you must also create a separate sandbox Requester account using your AWS root account email.

### Responses

All API calls to Amazon Mechanical Turk return an XML response. LaraTurk converts the XML to an array. Thus, for all LaraTurk calls, the returned object will be an array.

For example, the Mechanical Turk API documentation shows the following XML as a response for creating a HIT:

```xml
<CreateHITResponse>
  <OperationRequest>
    <RequestId>ece2785b-6292-4b12-a60e-4c34847a7916</RequestId>
  </OperationRequest>
  <HIT>
    <Request>
      <IsValid>True</IsValid>
    </Request>
    <HITId>GBHZVQX3EHXZ2AYDY2T0</HITId>
    <HITTypeId>NYVZTQ1QVKJZXCYZCZVZ</HITTypeId>
  </HIT>
</CreateHITResponse>
```

The returned array from LaraTurk will then look like so:

```php
[
   "OperationRequest" => [
       "RequestId" => "ece2785b-6292-4b12-a60e-4c34847a7916"
   ],
   "HIT"              => [
       "Request"   => [
           "IsValid" => "True"
       ],
       "HITId"     => "GBHZVQX3EHXZ2AYDY2T0",
       "HITTypeId" => "NYVZTQ1QVKJZXCYZCZVZ"
   ]
]
```

### Examples

Here are a couple examples of usage:

#### Create HIT with HITLayoutID and HITTypeID

This assumes a HIT Layout has been created on the Requester site and a HIT Type has been registered.

```php
$params = [
	'HITTypeID' => '',
	'HITLayoutId' => '',
	'HITLayoutParameter' => [
		[
		'Name' => 'image_title',
		'Value' => 'Some image'
		],
		[
			'Name' => 'description',
			'Value' => 'None'
		]
	],
	'LifetimeInSeconds' => 300,
	'MaxAssignments' => 3
];

$turk = new LaraTurk;
$response = $turk->forceExpireHIT($params);
```

The `$response` object is in the form of:

```php
[
   "OperationRequest" => [
       "RequestId" => "ece2785b-6292-4b12-a60e-4c34847a7916"
   ],
   "HIT"              => [
       "Request"   => [
           "IsValid" => "True"
       ],
       "HITId"     => "GBHZVQX3EHXZ2AYDY2T0",
       "HITTypeId" => "NYVZTQ1QVKJZXCYZCZVZ"
   ]
]
```

#### ForceExpireHIT

The API response is only a true/false response, so if a LaraTurkException is not thrown, then the request succeeded.

```php
$turk = new LaraTurk;
$response = $turk->forceExpireHIT(['HITId' => '3AQGTY5GMKYZ11S8P0G0J0DRP0MU70']);
```

The `$response` object is in the form of:

```php
[
   "OperationRequest" => [
       "RequestId" => "ece2785b-6292-4b12-a60e-4c34847a7916"
   ],
   "ForceExpireHITResult" => [
       "Request"   => [
           "IsValid" => "True"
       ]
   ]
]
```

### Special Parameter Formats

#### Reward

Set the `Reward` parameter like so:

```php
$params['Reward'] = [
	'Amount' => 0.07,
	'CurrencyCode' => 'USD',
	'FormattedPrice' => '$0.07' // optional parameter
];
```

#### Layout Parameters

Set the `HITLayoutParameter` parameter like so:

```php
$params['HITLayoutParameter'] = [
	[
		'Name' => 'image_title',
		'Value' => 'Some image'
	],
	[
		'Name' => 'description',
		'Value' => 'None'
	]
];
```

These correspond to the parameters you defined in your HIT Layout template.

#### Qualification Requirements

Set the `QualificationRequirement` parameter like so:

```php
$params['QualificationRequirement'] = [
	[
        'QualificationTypeId' => '00000000000000000071', // Worker locale qualification
        'Comparator' => 'In',
        'LocaleValue' => [
            [
                'Country' => 'US'
            ],
            [
                'Country' => 'CA'
            ]
        ] // located in the US or Canada
    ],
    [
        'QualificationTypeId' => '00000000000000000040', // Worker approved hits qualification
        'Comparator' => 'GreaterThanOrEqualTo',
        'IntegerValue' => '1000'
    ],
    [
        'QualificationTypeId' => '000000000000000000L0', // Worker approval percentage qualification
        'Comparator' => 'GreaterThanOrEqualTo',
        'IntegerValue' => '98'
    ]
];
```

This qualification would require the worker to be located in the US or Canada, have completed at least 1000 HITs, and have at least a 98% assignment approval percentage to be able to accept your HIT.

#### Notifications

Set the `Notification` parameter like so:

```php
$params['Notification'] = [
	[
		'Destination' => 'example@example.com',
		'Transport' => 'Email',
		'Version' => '2006-05-05',
		'EventType' => [
			'HITReviewable',
			'HITExpired'
		]
	],
	[
		'Destination' => 'foo@bar.com',
		'Transport' => 'Email',
		'Version' => '2006-05-05',
		'EventType' => [
			'AssignmentAccepted'
		]
	]
];
```

### Currently implemented features and associated functions

**HITs**
- [CreateHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_CreateHITOperation.html) with HIT Type ID and HIT Layout ID => `createHITByTypeIdAndByLayoutId()`
- [CreateHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_CreateHITOperation.html) with HIT Layout ID => `createHITByLayoutId()`
- [ChangeHITTypeOfHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_ChangeHITTypeOfHITOperation.html) => `changeHITTypeOfHIT()`
- [ExtendHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_ExtendHITOperation.html) => `extendHIT()`
- [ForceExpireHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_ForceExpireHITOperation.html) => `forceExpireHIT()`
- [DisableHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_DisableHITOperation.html) => `disableHIT()`
- [DisposeHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_DisposeHITOperation.html) => `disposeHIT()`
- [SetHITAsReviewing](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_SetHITAsReviewingOperation.html) => `setHITAsReviewing()`
- [GetReviewableHITs](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetReviewableHITsOperation.html) => `getReviewableHITs()`
- [SearchHITs](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_SearchHITsOperation.html) => `searchHITs()`
- [GetHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetHITOperation.html) => `getHIT()`
- [RegisterHITType](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_RegisterHITTypeOperation.html) => `registerHITType()`

**Assignments**
- [GetAssignmentsForHIT](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetAssignmentsForHITOperation.html) => `getAssignmentsForHIT()`
- [GetAssignment](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetAssignmentOperation.html) => `getAssignment()`
- [ApproveAssignment](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_ApproveAssignmentOperation.html) => `approveAssignment()`
- [RejectAssignment](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_RejectAssignmentOperation.html) => `rejectAssignment()`
- [ApproveRejectedAssignment](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_ApproveRejectedAssignmentOperation.html) => `approveRejectedAssignment()`
- [GrantBonus](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GrantBonusOperation.html) => `grantBonus()`
- [GetBonusPayments](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetBonusPaymentsOperation.html) => `getBonusPayments()`

**Notifications**
- [SetHITTypeNotification](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_SetHITTypeNotificationOperation.html) => `setHITTypeNotification()`
- [SendTestEventNotification](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMechanicalTurkRequester/Concepts_NotificationsArticle.html) => `sendTestEventNotification()`

**Workers**
- [BlockWorker](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_BlockWorkerOperation.html) => `blockWorker()`
- [UnblockWorker](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_UnblockWorkerOperation.html) => `unblockWorker()`
- [GetBlockedWorkers](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetBlockedWorkersOperation.html) => `getBlockedWorkers()`
- [GetRequesterWorkerStatistic](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetRequesterWorkerStatisticOperation.html) => `getRequesterWorkerStatistic()`
- [GetFileUploadURL](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetFileUploadURLOperation.html) => `getFileUploadURL()`
- [NotifyWorkers](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_NotifyWorkersOperation.html) => `notifyWorkers()`

**Requester Account**
- [GetAccountBalance](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetAccountBalanceOperation.html) => `getAccountBalance()`
- [GetRequesterStatistic](http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_GetRequesterStatisticOperation.html) => `getRequesterStatistic()`

*Notes:*
- Creating a HIT using a `QuestionForm` parameter is not currently supported. You must create a HIT Layout within Mechanical Turk (for both production and sandbox modes) and get the HITLayoutID.
- Creating a HIT with an `AssignmentReviewPolicy` and/or a `HITReviewPolicy` is not yet supported.

### Exceptions

If a required parameter is not found in the parameters passed or if the API call returns an error for any reason, LaraTurk will throw a `LaraTurkException`. This exception works just like the base `Exception` class in Laravel, but also includes an additional `getErrors()` function that will return the error array returned from the API call.

For example, if the API call returns an error because the AWS credentials were invalid and you catch the exception:

```php
...
catch(LaraTurkException $e)
{
	// your code here
}
```

Calling `$e->getMessage()` would return:
```
AWS credentials rejected.
```

Calling `$e->getErrors()` would return:
```php
"Error" => [
   "Code"    => "AWS.NotAuthorized",
   "Message" => "The identity contained in the request is not authorized to use this AWSAccessKeyId (5934433916915 s)"
]
```

## Repository
[https://github.com/pauly4it/laraturk](https://github.com/pauly4it/laraturk)

## Questions, Problems, Bugs

If you need any help with this package or find a bug, please submit an issue.

## Contributing

Feel free to submit a pull request! Please add a detailed description to help me understand your change. Thanks!

## License

The LaraTurk package is licensed under the [MIT license](http://opensource.org/licenses/MIT).
