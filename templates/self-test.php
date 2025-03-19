<?php use ChatGptSeo\ChatBot; ?>
<h1>
    Chat GPT SEO Self Test
</h1>
<?php
$tests = new \SeoAudit\Tests();
?>
<h2>
    Try send message
</h2>
<?php
$tests::TestApiRequest();
?>


<h2>Test creating the assistant</h2>
<?php
$tests::TestCreateAssistant();
?>


<h2>Get assistant</h2>
<?php
$tests::TestGetAssistant();
?>

<h2>Create thread</h2>
<?php
$tests::TestCreateThread();
?>



<h2>Get thread</h2>
<?php
$tests::TestGetThread();
?>


