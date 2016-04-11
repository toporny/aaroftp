<p>Thank you again for investing in our install service.</p>
<p>A fully working version of product/s:</br>
<ul>
@foreach($product_names as $product_name)
<li>{{ $product_name->pname }}</li>
@endforeach
</ul>
have/has just been installed on your server.</p>
<p>Please log into your admin area to get access to your links:</p>
<ul>
<li>{{ $website }}/admin</li>
<li>user: admin</li>
<li>pass: ssmachine</li>
</ul>
</p>

<p>Thanks,</p>

<p>SuperSalesMachine.com Team!</p>