<!-- Add this alert at the top of the admin verification page -->
<div class="alert alert-warning">
    <h5><i class="fas fa-exclamation-triangle"></i> Important Reminder</h5>
    <p>When customers use CBE QR payment, you need to:</p>
    <ol>
        <li>Check your CBE mobile banking app or account statement for new payments</li>
        <li>Verify the payment details against the booking information before confirming</li>
        <li>Ensure the transaction ID matches what the customer provided</li>
    </ol>
</div>
<!-- Display the dynamic amount in the verification table -->
<td>ETB <?php echo number_format($payment['payment_amount'], 2); ?> (5% of property price)</td>