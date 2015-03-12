<?php

abstract class AccountType{
    const Checking = 'Checking';
    const Savings = 'Savings';
}

abstract class DataEntryMode{
    const Manual = 'MANUAL';
    const Swipe = 'SWIPE';
}

abstract class CheckType{
    const Personal = 'PERSONAL';
    const Business = 'BUSINESS';
}

abstract class SECCode{
    const PPD = 'PPD';
    const CCD = 'CCD';
    const POP = 'POP';
    const WEB = 'WEB';
    const TEL = 'TEL';
    const EBronze = 'eBronze';
}
 