<!-- Add this alert at the top of the admin verification page -->
<div class="alert alert-warning">
    <h5><i class="fas fa-exclamation-triangle"></i> Important Reminder</h5>
    <p>When customers use Telebirr, they first send a friend/connection request by scanning the QR code. You need to:</p>
    <ol>
        <li>Check your Telebirr app regularly for new friend requests and accept them</li>
        <li>After accepting, the customer will send the actual payment</li>
        <li>Verify the payment details against the booking information before confirming</li>
    </ol>
</div>
<!-- Display the dynamic amount in the verification table -->
<td>ETB <?php echo number_format($payment['payment_amount'], 2); ?> (5% of property price)</td>