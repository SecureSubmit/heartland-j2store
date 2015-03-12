<?php


// Abstract Classes used as enums
// ACH Enums
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
    const Payroll = 'PAYROLL';
}

abstract class CheckActionType{
    const Checking = 'CHECKING';
    const Savings = 'SAVINGS';
}

abstract class SECCode{
    const PPD = 'PPD';
    const CCD = 'CCD';
    const POP = 'POP';
    const WEB = 'WEB';
    const TEL = 'TEL';
    const EBronze = 'eBronze';
}

// PayPlan Enums
abstract class HpsPayPlanCustomerStatus{
    const Active = 'Active';
    const Inactive = 'Inactive';
}

// Gift Card Enums
abstract class HpsGiftCardAliasAction{
    const Delete = 'DELETE';
    const Add = 'ADD';
    const Create = 'CREATE';
}

// General Enums
abstract class HpsTransactionType{
    const Authorize = 1;
    const Capture = 2;
    const Charge = 3;
    const Refund = 4;
    const Reverse = 5;
    const Verify = 6;
    const ListTransaction = 7;
    const Get = 8;
    const Void = 9;
    const SecurityError = 10;
    const BatchClose = 11;
}

abstract class HpsExceptionCodes{
    // general codes
    const AuthenticationError = 0;
    const InvalidConfiguration = 1;

    // input codes
    const InvalidAmount = 2;
    const MissingCurrency = 3;
    const InvalidCurrency = 4;
    const InvalidDate = 5;
    const MissingCheckName = 27;

    // gateway codes
    const UnknownGatewayError = 6;
    const InvalidOriginalTransaction = 7;
    const NoOpenBatch = 8;
    const InvalidCpcData = 9;
    const InvalidCardData = 10;
    const InvalidNumber = 11;
    const GatewayTimeout = 12;
    const UnexpectedGatewayResponse = 13;
    const GatewayTimeoutReversalError = 14;

    // credit issuer codes
    const IncorrectNumber = 15;
    const ExpiredCard = 16;
    const InvalidPin = 17;
    const PinEntriesExceeded = 18;
    const InvalidExpiry = 19;
    const PinVerification = 20;
    const IssuerTimeout = 21;
    const IncorrectCvc = 22;
    const CardDeclined = 23;
    const ProcessingError = 24;
    const IssuerTimeoutReversalError = 25;
    const UnknownCreditError = 26;
}


abstract class HpsItemChoiceTypePosResponseVer10Transaction{
    const AddAttachment = "AddAttachment";
    const Authenticate = "Authenticate";
    const BatchClose = "BatchClose";
    const CancelImpersonation = "CancelImpersonation";
    const CheckSale = "CheckSale";
    const CheckVoid = "CheckVoid";
    const CreditAccountVerify = "CreditAccountVerify";
    const CreditAddToBatch = "CreditAddToBatch";
    const CreditAuth = "CreditAuth";
    const CreditCPCEdit = "CreditCPCEdit";
    const CreditIncrementalAuth = "CreditIncrementalAuth";
    const CreditOfflineAuth = "CreditOfflineAuth";
    const CreditOfflineSale = "CreditOfflineSale";
    const CreditReturn = "CreditReturn";
    const CreditReversal = "CreditReversal";
    const CreditSale = "CreditSale";
    const CreditTxnEdit = "CreditTxnEdit";
    const CreditVoid = "CreditVoid";
    const DebitAddValue = "DebitAddValue";
    const DebitReturn = "DebitReturn";
    const DebitReversal = "DebitReversal";
    const DebitSale = "DebitSale";
    const EBTBalanceInquiry = "EBTBalanceInquiry";
    const EBTCashBackPurchase = "EBTCashBackPurchase";
    const EBTCashBenefitWithdrawal = "EBTCashBenefitWithdrawal";
    const EBTFSPurchase = "EBTFSPurchase";
    const EBTFSReturn = "EBTFSReturn";
    const EBTVoucherPurchase = "EBTVoucherPurchase";
    const EndToEndTest = "EndToEndTest";
    const FindTransactions = "FindTransactions";
    const GetAttachments = "GetAttachments";
    const GetUserDeviceSettings = "GetUserDeviceSettings";
    const GetUserSettings = "GetUserSettings";
    const GiftCardActivate = "GiftCardActivate";
    const GiftCardAddValue = "GiftCardAddValue";
    const GiftCardBalance = "GiftCardBalance";
    const GiftCardCurrentDayTotals = "GiftCardCurrentDayTotals";
    const GiftCardDeactivate = "GiftCardDeactivate";
    const GiftCardPreviousDayTotals = "GiftCardPreviousDayTotals";
    const GiftCardReplace = "GiftCardReplace";
    const GiftCardReversal = "GiftCardReversal";
    const GiftCardSale = "GiftCardSale";
    const GiftCardVoid = "GiftCardVoid";
    const Impersonate = "Impersonate";
    const InvalidateAuthentication = "InvalidateAuthentication";
    const ManageSettings = "ManageSettings";
    const ManageUsers = "ManageUsers";
    const PrePaidAddValue = "PrePaidAddValue";
    const PrePaidBalanceInquiry = "PrePaidBalanceInquiry";
    const RecurringBilling = "RecurringBilling";
    const ReportActivity = "ReportActivity";
    const ReportBatchDetail = "ReportBatchDetail";
    const ReportBatchHistory = "ReportBatchHistory";
    const ReportBatchSummary = "ReportBatchSummary";
    const ReportOpenAuths = "ReportOpenAuths";
    const ReportSearch = "ReportSearch";
    const ReportTxnDetail = "ReportTxnDetail";
    const SendReceipt = "SendReceipt";
    const TestCredentials = "TestCredentials";
}