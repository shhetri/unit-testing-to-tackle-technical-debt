<?php
/**
 * Rule 2 : Naming
 */

$mf             = new Form(); //is bad
$monitoringForm = new Form(); //is good

//this is bad
foreach ($people as $x) {
    echo $x->name;
}

//this is good
foreach ($people as $person) {
    echo $person->name;
}

public function validateCreate() {} //is verbose
public function validate($type = 'create') {} //than this

$user->createUser(); //is redundant
$user->create(); //is better

---------------------------------------------------------------------------------------------------------

/**
 * Rule 3: Expressive code
 */

//this is how we normally code
$account = new Account($name, $address);
$account->save();

//this is how a customer speaks
Account::open($name, $address)->save();

//example from laravel
$schedule->command('clear-history')->hourly();

//this code works, I know
if ('paid' === $application->status) {
    //process paid application
}

//better code is
if ($application->isPaid()) {
    //process paid applicaiton
}

//in application class
public function isPaid()
{
    return 'paid' === $this->status;
}

//working with date
$day = Carbon::today()->dayOfWeek;

if (7 == $day) {
    return 'It is holiday';
}

//this makes much more sense
const SATURDAY = 7;
$today = Carbon::today()->dayOfWeek;

if (self::SATURDAY == $today) {
    return 'It is holiday';
}

---------------------------------------------------------------------------------------------------------

/**
 * Rule 4: Indentation
 */
 
//too many if else branch
public function getClientContact($type)
{
    if ('mobile' === $type) {
        $contact = 'mobile number';
    } elseif ('work' === $type) {
        $contact = 'work number';
    } elseif ('home' === $type) {
        $contact = 'home number';
    } else {
        $contact = false;
    }

    return $contact;
}

//else can be avoided with early return
public function getClientContact($type)
{
    if ('mobile' === $type) {
        return 'mobile number';
    }

    if ('work' === $type) {
        return 'work number';
    }

    if ('home' === $type) {
        return 'home number';
    }

    throw new Exception('Invalid argument type');
}

//3 level of indentation, need to refactor
public function filterAccounts(array $accounts, $accountType)
{
    $filteredAccounts = [];
    //0
    foreach ($accounts as $account) {
        //1
        if ($account->type === $accountType) {
            //2
            if ($account->status === 'active') {
                //3
                $filteredAccounts[] = $account;
            }
        }
    }

    return $filteredAccounts;
}

//reduced to 2 level
public function filterAccounts(array $accounts, $accountType)
{
    $filteredAccounts = [];
    //0
    foreach ($accounts as $account) {
        //1
        if ($account->type === $accountType && $account->status === 'active') {
            //2
            $filteredAccounts[] = $account;
        }
    }

    return $filteredAccounts;
}

//reduced to 1
public function filterAccounts($accounts, $accountType)
{
    //0
    return array_filter($accounts, function ($account) use ($accountType) {
        //1
        return $account->isOfType($accountType);
    });
}

---------------------------------------------------------------------------------------------------------

/**
 * Rule 6: DRY (Don't Repeat Yourself)
 */
class AccountService
{
    // this method will expand as the type of account adds up
    // the same logic will have to be used in update, show and may be in so many other places
    // the ladder of if else will just keep on increasing
    public function save($type, array $details)
    {
        if ('saving' === $type) {
            //code to save saving account
        } elseif ('current' === $type) {
            //code to save current account
        } else {
            //code to save default account
        }
    }
}

// why not leverage polymorphism
interface AccountInterface
{
    public function save(array $details);
}

class SavingAccount implements AccountInterface
{
    public function save(array $details)
    {
        //code to save saving account
    }
}

class CurrentAccount implements AccountInterface
{
    public function save(array $details)
    {
        //code to save current account
    }
}

class AccountService
{
    // we have eliminated the if else ladder
    // we have cleaned up the method to one liner
    // the logic to save different accounts resides in their respective classes
    // adding a new account is very easy
    public function save(AccountInterface $account, array $details)
    {
        $account->save($details);
    }
}

class AccountFactory
{
    private function __construct(){}

    public static function makeAccount($type)
    {
        if ('saving' === $type) {
            return new SavingAccount();
        }
    
        if ('current' === $type) {
            return new CurrentAccount();
        }
    
        return new DefaultAccount();
    }
}

// use traits to avoid duplication
trait FiltersByRole
{
    public function scopeFilterByRole($query, $callable = [])
    {
        $loggedInUser = auth()->user();

        if (isAdmin($loggedInUser)) {
            return $query;
        }

        if (is_callable($callable)) {
            return $this->call([$callable], $query, $loggedInUser, 0);
        }

        if (isBranchUser($loggedInUser)) {
            return $this->isCallableForBranch($callable)
                ? $this->call($callable, $query, $loggedInUser, 'branch')
                : $this->filterByBranch($query, $loggedInUser->branch_id);
        }

        return $this->isCallableForAgent($callable)
            ? $this->call($callable, $query, $loggedInUser, 'agent')
            : $this->filterByAgent($query, $loggedInUser->id);
    }
}

---------------------------------------------------------------------------------------------------------

/**
 * Rule 7: Extract commented code to a method
 */

//commented code is bad practice
public function printStatement(Account $account)
{
    // if the account is a saving account and if it is active then pring the statement
    if ('saving' === $account->type && 'active' === $account->status) {
        //print the statement
    }

    //  we return false, if the conditions for printing the account statement is not satisfied
    return false;
}

// this is better
public function printStatement(Account $account)
{
    if ($account->isTypeSaving() && $account->isActive()) {
        //print the statement
    }

    throw new Exception('Statement of this account can not be printed.');
}

// we can take it one step further
public function printStatement(Account $account)
{
    if ($account->isStatementPrintable()) {
        //print the statement
    }

    throw new Exception('Statement of this account can not be printed.');
}