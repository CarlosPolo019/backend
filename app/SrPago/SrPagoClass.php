<?php
include_once '/home/forge/dreamfactory.technisupport.com/vendor/srpago_php/init.php';

// \SrPago\SrPago::$apiKey = "sk_dev_5dfea093b3f2947e6c09a4f164b99b19d"; // Sandbox
\SrPago\SrPago::$apiKey = "sk_live_5e18d5382119abd45ba1875064f33942";
// \SrPago\SrPago::$apiSecret = "3z2NLJ?taWWN"; // Sandbox
\SrPago\SrPago::$apiSecret = "ORcbT=JZgM5_";
// \SrPago\SrPago::$liveMode = false; //Sandbox
\SrPago\SrPago::$liveMode = true;

class SrPago
{

    public function addCustomer($email, $name)
    {
        $customerService = new \SrPago\Customer();
        $data["email"] = $email;
        $data["name"] = $name;
        $r = $customerService->create($data);
        if ($r["success"] === true) {
            return $r["result"]["id"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function getAllCustomer()
    {
        $customerService = new \SrPago\Customer();
        $r = $customerService->all();
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function removeCustomer($id)
    {
        $customerService = new \SrPago\Customer();
        $r = $customerService->remove($id);
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function getCustomerById($id)
    {
        $customerService = new \SrPago\Customer();
        $r = $customerService->find($id);
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function getPaymentMethodsByCustomerId($id)
    {
        $customerCardService = new \SrPago\CustomerCards();
        $r = $customerCardService->all($id);
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function addPaymentMethodToCustomer($id, $token)
    {
        $customerCardService = new \SrPago\CustomerCards();
        $r = $customerCardService->add($id, $token);
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }

    }

    public function removePaymentMethodToCustomer($id, $token)
    {
        $customerCardService = new \SrPago\CustomerCards();
        $r = $customerCardService->remove($id, $token);
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }

    }

    public function generateToken($name, $number, $exp_year, $exp_month, $cvv)
    {
        $tokenService = new \SrPago\Token();
        $data["cardholder_name"] = $name;
        $data["number"] = $number;
        $data["expiration"] = $exp_year . $exp_month;
        $data["cvv"] = $cvv;
        $r = $tokenService->create($data);
        if ($r["success"] === true) {
            return $r["result"]["token"];
        } else {
            return "Error: " . $r["error"]["message"];
        }

    }

    public function getAllCharges()
    {
        $chargesService = new \SrPago\Charges();
        $r = $chargesService->all();
        if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

    public function charge($amount, $description, $reference, $source, $email, $firstName, $lastName, $phone, $address,$address1, $postalCode)
    {
        $chargesService = new \SrPago\Charges();
        $data["amount"] = $amount;
        $data["description"] = $description;
        $data["reference"] = $reference;
        $data["source"] = $source;
        $data["metadata"] = array(
            "items" => array(
                "item" => array(array(
                    "itemNumber" => "826262",
                    "itemDescription" => "Saldo Vanana",
                    "itemPrice" => $amount,
                    "itemQuantity" => "1",
                    "itemMeasurementUnit" => "Service",
                    "itemBrandName" => "Vanana",
                    "itemCategory" => "Services",
                    "itemTax" => "0",
                )),
            ),
            "billing" => array(
                "billingEmailAddress" => $email,
                "billingFirstName-D" => $firstName,
                "billingLastName-D" => $lastName,
                "billingAddress-D" => $address,
                "billingPhoneNumber-D" => $phone,
                "billingAddress2-D" => $address1,
                "billingCity-D" => "CDMX",
                "billingState-D" => "MEX",
                "billingPostalCode-D" => $postalCode,
                "billingCountry-D" => "MX",
                "creditCardAuthorizedAmount-D" => "2000",
            ),
            "member" => array(
                "memberLoggedIn" => "Si",
                "memberId" => "0",
                "membershipDate" => date("Y-m-d"),
                "memberFullName" => $firstName . " " . $lastName,
                "memberFirstName" => $firstName,
                "memberMiddleName" => $firstName,
                "memberLastName" => $lastName,
                "memberPhone" => $phone,
                "memberEmailAddress" => $email,
                "memberAddressLine1" => $address,
                "memberAddressLine2" => $address1,
                "memberCity" => "CDMX",
                "memberState" => "MEX",
                "memberCountry" => "MX",
                "memberPostalCode" => $postalCode,
                "membershipLevel" => "1",
                "membershipStatus" => "activo",
                "latitude" => "19.432608",
                "longitude" => "-99.133209",
            ),
        );

//return $data;

        $r = $chargesService->create($data);

       if ($r["success"] === true) {
            return $r["result"];
        } else {
            return "Error: " . $r["error"]["message"];
        }
    }

}

