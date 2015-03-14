Integrating PHP Authorizer to your web service or web page is simple as adding the following block of code:

```
$credit = 1;
if (AuthorizerClient::hasCredit("MyService", "MyKey", $credit)) {
    // visitor is authorized to use the service
} else {
    // visitor is not authorized to use the service
}
```

<br />
**Example 1: limit the number of your website requests to 30 per IP address per day**

First you need to enter default values (service name, maximum number of requests and number of days after which the counters are reset) in SQL table named `ServiceDefaults`. For this example, the following SQL statement does the right thing:

```
INSERT INTO database_name.ServiceDefaults
VALUES (NULL, 'MySite', 30, 1); 
```

Next, you need to customize `./client/class/AuthorizerClient.class.php` file. In line 51 replace default URL with the URL containing your hostname and path to PHP Authorization web service. Keep the trailing slash and question mark.

After this, run `./client/example1.php` script. If you have set up everything correctly, you should see the message _'You can use the web site!'_. If you open `ClientData` table you will notice that it looks like:

| **ID** | **service** | **address** | **key** | **usedCredit** | **maxCredit** | **expireInDays** | **timestamp** |
|:-------|:------------|:------------|:--------|:---------------|:--------------|:-----------------|:--------------|
| 1 |	MySite | 127.0.0.1 | default | 1 | 30 | 1 | 1245149253 |

If you re-run the script you will notice that `usedCredit` is increased by one, etc. After you have used up the limit that you have set, error message will appear.

Basically, `AuthorizerClient.class.php` file and code snippet from `example1.php` is all that you need to integrate in your web site to use PHP Authorization service. Each visit to your web site from different IP address will have it's own entry in `ClientData` table and thus own request counters.

<br />
**Example 2: set the fixed number of your web service requests for particular customer**

In this case we don't set any entries in `ServiceDefaults` table. We have a particular customer that has subscribed to particular web service for a particular number of requests, so we set everything in `ClientData` table. For this example we will use the following SQL statement:

```
INSERT INTO database_name.ClientData VALUES (
NULL, 'MyService', '*', 'SecretKey', 0, 1000, 0,
UNIX_TIMESTAMP(NOW( )));
```

Basically, we have allowed customer to use the service named `MyService` from any IP address, using `SecretKey` as authentication string. User has currently made 0 out of 1000 allowed requests and there is no time constraint for usage of this web service. The last parameter `timestamp` is mandatory, so we set it to be the time of creation of this entry.

After this, run the `./client/example2.php` script. If you check the `ClientData` table now it will look like:


| **ID** | **service** | **address** | **key** | **usedCredit** | **maxCredit** | **expireInDays** | **timestamp** |
|:-------|:------------|:------------|:--------|:---------------|:--------------|:-----------------|:--------------|
| 1 |	MySite | 127.0.0.1 | default | 1 | 30 | 1 | 1245149253 |
| 1 |	MyService | `*` | SecretKey | 1 | 1000 | 0 | 1245169132 |

So again, `AuthorizerClient.class.php` file and code snippet from `example2.php` is all that you need to integrate in your web service to use PHP Authorization. Each customer will be identified by it's own key and each customer will have it's own counter incremented on each request that he makes.

A final note on some special values ... obviously if `expireInDays` is set to 0, counters do not expire. If `maxCredit` is set to 0, user is blocked from making requests. However, if it is set to negative value - user has unlimited number of requests available.