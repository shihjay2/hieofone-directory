# HIE of One Directory

HIE of One Directory is a simple server that incorporates [OAuth2](https://tools.ietf.org/html/rfc6749) and [OpenID Connect](https://openid.net/connect/) protocols to facilitate the aggregation of multiple [HIE of One Authorization Servers](https://github.com/shihjay2/hieofone-as) for clients such as physician groups, hospitals, health co-operatives, and third-party health service providers.  In effect, a personalized directory of authorized HIE Of One Authorization servers provides the requesting party a "one-stop-shop" for their list of patients for practice work flow management.  The only personal information that is sent from the **authorization server** to the **directory** is the root URL of the authorization server and the name of the authorization server which will contain the name of the patient.

## Installation
Run the following commands to install:

	sudo curl -o install.sh https://raw.githubusercontent.com/shihjay2/hieofone-directory/master/install.sh  
	sudo chmod +x install.sh  
	sudo bash install.sh

## Dependencies
1. PHP
2. MySQL
3. Apache
4. CURL

## Features
1. OAuth2 OpenID Connect compliant server
2. OAuth2 OpenID Connect relying party for Google and Twitter

## How a patient registers with the HIE of One Directory

### Requisite conditions:
1. Patient has registered a domain name where the HIE of One Authorization Server is installed (ie domain.xyz)
2. Client software (such as an EHR with a patient portal) has the capability to make HTTPS calls (such as CURL) and is able to process JSON responses.

### Step 1:
Patient logs into their HIE of One Authorization Server and authorizes participation to a **directory**

### Step 2:
Patient clicks on Register to a Directory (proposed)

### Step 3a:
If the HIE of One Authorization server belongs on a sub-domain that is created by a root domain that has an HIE of One Directory present, a CURL request will be made to verify if a **directory** exists.  If so, the **directory** URL and name will be listed for patient's approval.

### Step 3b:
If a specific **directory** is not located in the root domain of the patient's **authorization server**, then the patient will need to either visit the **directory** service URL (via a QR code) or manually enter the URL of the **directory** root URL in the URL box after Step 2.

### Step 4:
If authorized, a call to the directory's registration route which will then dynamically register the **authorization server** to the **directory**.

## Security Vulnerabilities

If you discover a security vulnerability within HIE of One Directory, please send an e-mail to Michael Chen at shihjay2 at gmail.com. All security vulnerabilities will be promptly addressed.

## License

The HIE of One Directory is open-sourced software licensed under the [GNU AGPLv3 license](https://opensource.org/licenses/AGPL-3.0).
