<?php
declare(strict_types=1);

/**
 * PSA e-payment standard (EPS) implementation for PHP
 *
 * Copyright 2026 PSA Payment Services Austria GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Knusperleicht\EpsBankTransfer\Requests\Mappers;

use DateTime;
use Exception;
use Knusperleicht\EpsBankTransfer\Internal\Generated\AustrianRules\AustrianRulesDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\BeneficiaryPartyDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\BfiPartyDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\EpiDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\IdentificationDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\InstructedAmount;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\PartyDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Epi\PaymentInstructionDetails;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Payment\V26\PaymentInitiatorDetails as PaymentInitiatorDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\AuthenticationDetails as AuthenticationDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\EpsProtocolDetails as EpsProtocolDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransactionNokUrl as TransactionNokUrlV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransactionOkUrl as TransactionOkUrlV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferInitiatorDetails as TransferInitiatorDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\TransferMsgDetails as TransferMsgDetailsV26;
use Knusperleicht\EpsBankTransfer\Internal\Generated\Protocol\V26\WebshopArticle as WebshopArticleV26;
use Knusperleicht\EpsBankTransfer\Requests\TransferInitiatorDetails;

/**
 * Maps domain TransferInitiatorDetails to EPS v2.6 generated tree.
 */
class TransferInitiatorDetailsV26Mapper
{
    /**
     * @throws Exception
     */
    public static function map(TransferInitiatorDetails $src): EpsProtocolDetailsV26
    {
        $xml = new EpsProtocolDetailsV26();
        $xml->setSessionLanguage('DE');

        $transferInitiatorDetails = new TransferInitiatorDetailsV26();

        $paymentInitiatorDetails = new PaymentInitiatorDetailsV26();
        $transferInitiatorDetails->setPaymentInitiatorDetails($paymentInitiatorDetails);

        $transferMsgDetails = new TransferMsgDetailsV26();
        $transferMsgDetails->setConfirmationUrl($src->getTransferMsgDetails()->getConfirmationUrl());

        $transactionOkUrl = new TransactionOkUrlV26($src->getTransferMsgDetails()->getTransactionOkUrl());
        $transferMsgDetails->setTransactionOkUrl($transactionOkUrl);

        $transactionNokUrl = new TransactionNokUrlV26($src->getTransferMsgDetails()->getTransactionNokUrl());
        $transferMsgDetails->setTransactionNokUrl($transactionNokUrl);

        $transferInitiatorDetails->setTransferMsgDetails($transferMsgDetails);

        if (!empty($src->getWebshopArticles())) {
            $articles = [];
            foreach ($src->getWebshopArticles() as $article) {
                $webshopArticle = new WebshopArticleV26();
                $webshopArticle->setArticleName($article->name);
                $webshopArticle->setArticleCount($article->count);
                $webshopArticle->setArticlePrice($article->price);
                $articles[] = $webshopArticle;
            }
            $transferInitiatorDetails->setWebshopDetails($articles);
        }

        $authenticationDetails = new AuthenticationDetailsV26();
        $authenticationDetails->setUserId($src->getUserId());
        $authenticationDetails->setMD5Fingerprint($src->getMD5Fingerprint());
        $transferInitiatorDetails->setAuthenticationDetails($authenticationDetails);

        $epiDetails = new EpiDetails();
        $identificationDetails = new IdentificationDetails();
        $partyDetails = new PartyDetails();
        $paymentInstructionDetails = new PaymentInstructionDetails();

        if ($src->getUnstructuredRemittanceIdentifier() === null) {
            $paymentInstructionDetails->setRemittanceIdentifier((string)$src->getRemittanceIdentifier());
        } else {
            $paymentInstructionDetails->setUnstructuredRemittanceIdentifier((string)$src->getUnstructuredRemittanceIdentifier());
        }

        $instructedAmount = new InstructedAmount($src->getInstructedAmount());
        $instructedAmount->setAmountCurrencyIdentifier($src->getAmountCurrencyIdentifier());
        $paymentInstructionDetails->setInstructedAmount($instructedAmount);
        $paymentInstructionDetails->setChargeCode('SHA');

        $bfiPartyDetails = new BfiPartyDetails();
        $bfiPartyDetails->setBfiBicIdentifier($src->getBfiBicIdentifier());
        $partyDetails->setBfiPartyDetails($bfiPartyDetails);

        $beneficiaryPartyDetails = new BeneficiaryPartyDetails();
        $beneficiaryPartyDetails->setBeneficiaryNameAddressText($src->getBeneficiaryNameAddressText());
        $beneficiaryPartyDetails->setBeneficiaryAccountIdentifier($src->getBeneficiaryAccountIdentifier());
        $partyDetails->setBeneficiaryPartyDetails($beneficiaryPartyDetails);

        $identificationDetails->setDate($src->getDate());
        $identificationDetails->setReferenceIdentifier($src->getReferenceIdentifier());

        if (!empty($src->getOrderingCustomerOfiIdentifier())) {
            $identificationDetails->setOrderingCustomerOfiIdentifier($src->getOrderingCustomerOfiIdentifier());
        }

        $epiDetails->setIdentificationDetails($identificationDetails);
        $epiDetails->setPartyDetails($partyDetails);
        $epiDetails->setPaymentInstructionDetails($paymentInstructionDetails);
        $paymentInitiatorDetails->setEpiDetails($epiDetails);

        $austrianRulesDetails = new AustrianRulesDetails();
        $austrianRulesDetails->setDigSig('SIG');

        if (!empty($src->getExpirationTime())) {
            $austrianRulesDetails->setExpirationTime(new DateTime($src->getExpirationTime()));
        }

        $paymentInitiatorDetails->setAustrianRulesDetails($austrianRulesDetails);

        $xml->setTransferInitiatorDetails($transferInitiatorDetails);

        return $xml;
    }
}
