# Stripe Payment Gateway Setup Guide

## Overview
This guide will help you set up Stripe as a payment gateway in your EHR system. Stripe allows you to accept credit card payments, digital wallets (Apple Pay, Google Pay), and bank transfers.

## Prerequisites
1. A Stripe account (sign up at https://stripe.com)
2. Admin access to your EHR system
3. Your Stripe API keys (from Stripe Dashboard)

---

## Step 1: Get Your Stripe API Keys

### For Testing (Test Mode)
1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com)
2. Make sure you're in **Test mode** (toggle in the top right)
3. Navigate to **Developers** → **API Keys**
4. You'll see two keys:
   - **Publishable key** (starts with `pk_test_...`)
   - **Secret key** (starts with `sk_test_...`) - Click "Reveal test key" to see it
5. Copy both keys - you'll need them in Step 2

### For Production (Live Mode)
1. In Stripe Dashboard, switch to **Live mode** (toggle in top right)
2. Navigate to **Developers** → **API Keys**
3. Get your **Live** publishable key and secret key
4. **Important**: Only use live keys when you're ready to accept real payments

---

## Step 2: Configure Stripe in Admin Panel

1. **Navigate to Payment Gateways**
   - Go to: `/admin/payment-gateways`
   - Or: Admin Dashboard → Payment Gateways

2. **Click "Add New Gateway"** or **"Add First Gateway"**

3. **Fill in the Gateway Information:**
   - **Gateway Provider**: Select **Stripe** from the dropdown
   - **Display Name**: Enter a friendly name (e.g., "Stripe Payments" or "Credit Card Payments")
   - **Description**: Optional description (e.g., "Accept credit cards, Apple Pay, and Google Pay")
   - **Sort Order**: Leave as 0 (or set priority if you have multiple gateways)
   - **Transaction Fee (%)**: Optional - percentage fee (e.g., 2.9 for 2.9%)
   - **Fixed Fee**: Optional - fixed amount per transaction (e.g., 0.30 for $0.30)

4. **Gateway Settings:**
   - ✅ **Active**: Check this to enable the gateway
   - ⭐ **Default**: Check this if this should be the default payment method
   - 🧪 **Test Mode**: 
     - ✅ Check for testing (use test API keys)
     - ❌ Uncheck for production (use live API keys)

5. **Enter API Credentials:**
   - **Publishable Key**: Paste your Stripe publishable key (`pk_test_...` or `pk_live_...`)
   - **Secret Key**: Paste your Stripe secret key (`sk_test_...` or `sk_live_...`)
   - **Webhook Secret**: (Optional for now) Leave empty for basic testing

6. **Click "Save Gateway"**

---

## Step 3: Test the Connection

1. After saving, you'll see your gateway in the list
2. Click the **Test Connection** button (WiFi icon) to verify your API keys are correct
3. You should see: ✅ "Connection test successful!"

---

## Step 4: Set Up Webhooks (Optional but Recommended)

Webhooks allow Stripe to notify your system when payments are completed, failed, or refunded.

### Get Webhook URL
1. Your webhook URL is automatically generated: `https://yourdomain.com/webhooks/stripe`
2. This URL is shown in the "Webhook Configuration" section when creating/editing the gateway

### Configure in Stripe Dashboard
1. Go to **Stripe Dashboard** → **Developers** → **Webhooks**
2. Click **"Add endpoint"**
3. Enter your webhook URL: `https://yourdomain.com/webhooks/stripe`
4. Select events to listen for:
   - ✅ `payment_intent.succeeded`
   - ✅ `payment_intent.payment_failed`
   - ✅ `checkout.session.completed`
5. Click **"Add endpoint"**
6. Copy the **Signing secret** (starts with `whsec_...`)
7. Go back to your EHR admin panel → Edit the Stripe gateway
8. Paste the webhook secret in the **Webhook Secret** field
9. Save the gateway

---

## Step 5: Test a Payment

### Using Test Mode
1. Make sure **Test Mode** is enabled in your gateway settings
2. Use Stripe's test card numbers:
   - **Success**: `4242 4242 4242 4242`
   - **Decline**: `4000 0000 0000 0002`
   - **3D Secure**: `4000 0025 0000 3155`
3. Use any future expiry date (e.g., 12/25)
4. Use any 3-digit CVC (e.g., 123)
5. Use any ZIP code (e.g., 12345)

### Test Payment Flow
1. Create a billing record for a patient
2. Send the billing email to the patient
3. Patient clicks the payment link
4. Select Stripe as the payment method
5. Enter test card details
6. Complete the payment
7. Verify payment status updates in your system

---

## Step 6: Go Live

When you're ready to accept real payments:

1. **Get Live API Keys**
   - Switch to Live mode in Stripe Dashboard
   - Get your live publishable and secret keys

2. **Update Gateway Settings**
   - Edit your Stripe gateway in admin panel
   - Uncheck **Test Mode**
   - Update API credentials with live keys:
     - Replace `pk_test_...` with `pk_live_...`
     - Replace `sk_test_...` with `sk_live_...`
   - Update webhook secret with live webhook signing secret
   - Save changes

3. **Test with Small Amount**
   - Process a small real payment first
   - Verify everything works correctly
   - Monitor Stripe Dashboard for transactions

---

## Supported Payment Methods

With Stripe configured, your system can accept:
- ✅ Credit Cards (Visa, Mastercard, Amex, Discover)
- ✅ Debit Cards
- ✅ Apple Pay
- ✅ Google Pay
- ✅ Bank Transfers (ACH)

---

## Troubleshooting

### Connection Test Fails
- ✅ Verify API keys are correct (no extra spaces)
- ✅ Check if you're using test keys with test mode enabled
- ✅ Ensure your Stripe account is active
- ✅ Check internet connection

### Payments Not Processing
- ✅ Verify gateway is set to "Active"
- ✅ Check if gateway is set as "Default"
- ✅ Ensure test mode matches your API keys (test keys = test mode ON)
- ✅ Check Stripe Dashboard for error logs

### Webhooks Not Working
- ✅ Verify webhook URL is accessible (HTTPS required)
- ✅ Check webhook secret is correct
- ✅ Ensure selected events match what your system expects
- ✅ Check Stripe Dashboard → Webhooks → Recent events

### Payment Status Not Updating
- ✅ Verify webhooks are configured correctly
- ✅ Check webhook secret matches
- ✅ Review Laravel logs for webhook processing errors
- ✅ Ensure database connection is working

---

## Security Best Practices

1. **Never share your secret keys** - They should only be in your admin panel
2. **Use HTTPS** - Required for webhooks and secure payments
3. **Rotate keys regularly** - Update API keys if compromised
4. **Monitor transactions** - Regularly check Stripe Dashboard
5. **Use test mode** - Always test in test mode before going live
6. **PCI Compliance** - Stripe handles PCI compliance, but ensure your system is secure

---

## Additional Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Stripe Test Cards](https://stripe.com/docs/testing)
- [Stripe Webhooks Guide](https://stripe.com/docs/webhooks)
- [Stripe Dashboard](https://dashboard.stripe.com)

---

## Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Check Stripe Dashboard for transaction details
3. Review webhook events in Stripe Dashboard
4. Contact your system administrator

---

**Last Updated**: {{ date('Y-m-d') }}

