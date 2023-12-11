<style>
    ul.soe-chat-gpt-keyword-list li {
        padding-left: 30px;
    }

    .kw-found-yes, .kw-found-some-are-missing, .kw-found-ok {
        color: green;
        padding: 15px;
    }


    .kw-found-no, .kw-found-missing {
        color: red;
        padding: 15px;
    }

    .kw-found-mostly-missing {
        color: orangered;
        padding: 15px;
    }

    .chat-gpt-seo-table {
        background-color: white;
        border-radius: 10px;
    }

    .chat-gpt-seo-table th,
    .chat-gpt-seo-table td {
        padding: 7px 15px;
    }

    .chat-gpt-seo-table th {
        font-size: 14px;
    }

    .chat-gpt-seo-table tr:nth-child(2n) {
        background-color: lightblue;
    }

    .seo-more-details {
        overflow: hidden;
        height: 0;
        display: none;
    }

    .seo-more-details.show {
        height: auto;
        display: table-row;
    }

    .seo-more-info-container {
        margin: auto;
        padding: 30px;
        max-width: 600px;
    }

    .seo-more-info-container textarea {
        width: 100%;
        height: 400px;
    }

    .button-row {
        display: flex;
        justify-content: space-between;
        padding: 15px;
    }

    .seo-more-info-container a {

        cursor: pointer;
        width: auto;

        display: inline-block;
        padding: 15px 30px;
        border-radius: 6px;
        background-color: lightgreen;
        border: 1px solid green;
        margin: 15px auto 15px autp;
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        color: black;
        text-decoration: none;
    }

    .seo-more-info-container p {
        font-size: 14px;
    }

    .seo-more-info-container p span {
        font-size: 12px;
    }

    .seo-more-info-container a.secondary {
        background-color: lightgrey;
        border: 1px solid grey;
    }

    .keyword-list ul, .keyword-list li {
        width: 100%;
        display: block;
    }

    .keyword-list ul > li > ul > li {
        padding-left: 30px;
        display: block;
        width: 100%;
    }

    ul.found-keywords {
        margin: 0;


    }

    ul.found-keywords li {
        margin: 0;
        padding: 0;
        padding-left: 15px;
    }

    ul.found-in-places li {
        padding-left: 30px;
        font-size: 12px;
    }
</style>

<script>


  function httpPost(url, headers, data, callback, failCallBack) {

    var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.readyState === XMLHttpRequest.DONE) { // XMLHttpRequest.DONE == 4
        if (xmlhttp.status === 200) {
          callback(JSON.parse(xmlhttp.response), url, headers);
        } else {
          if (failCallBack) {
            failCallBack();
          }
        }
        clearTimeout(window.requestCancelTimer);
      }
    };
    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    if (headers && headers.headers) {
      for (const key of Object.keys(headers.headers)) {
        xmlhttp.setRequestHeader(key, headers.headers[key]);
      }
    }
    xmlhttp.send(JSON.stringify(data));
  }


  function httpGet(url, headers, callback, failCallBack) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.readyState === XMLHttpRequest.DONE) { // XMLHttpRequest.DONE == 4

        const status = xmlhttp.status;
        if (status === 200) {
          try {
            callback(JSON.parse(xmlhttp.response), url, headers, status);
          } catch (e) {
            callback({response: xmlhttp.response}, url, headers, status);
          }

        } else if (status === 204) {
          callback(null, url, headers, status);
        } else if (status === 401) {
          callback(null, url, headers, status);
        } else {
          if (failCallBack) {
            failCallBack(null, url, headers, status);
          }
        }
        clearTimeout(window.requestCancelTimer);
      }
    };

    xmlhttp.open("GET", url, true);
    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    if (headers && headers.headers) {
      for (const headersKey of Object.keys(headers.headers)) {
        xmlhttp.setRequestHeader(headersKey, headers.headers[headersKey]);
      }
    }

    xmlhttp.send();
  }


  function crawlData(passId) {
    var element = document.querySelector('.chat-gpt-seo-check-post');
    var id = passId;
    if (element) {
      element.classList.remove('chat-gpt-seo-check-post');
      id = element.getAttribute('data-id');
    }
    var force = false;
    if (passId) {
      force = true;
    }
    console.log("lets crawl", id);
    var url = "/wp-json/chat-gpt-seo/v1/audit-item/" + id;
    if (force) {
      url = "/wp-json/chat-gpt-seo/v1/force-audit-item/" + id;
    }
    console.log("url::", url);
    var status = document.querySelector('#penalty-' + id);
    if (status) {
      status.innerHTML = "...auditing...";
    }

    httpGet(url, null, (data) => {


      status.innerHTML = "...done...";
      console.log(data.id);

      var firstRow = document.querySelector('#seo-summary-' + data.id);
      console.log(firstRow);
      var secondRow = document.querySelector('#seo-more-details-' + data.id);
      console.log(secondRow);
      if (firstRow && secondRow) {
        firstRow.innerHTML = data.html.first_row_html;
        secondRow.innerHTML = data.html.second_row_html;
      }

      var element = document.querySelector('.chat-gpt-seo-check-post');


      if (element && !force) {
        crawlData();
      }
    });
  }

  setTimeout(function () {
    crawlData();
  }, 3000)


  function expandReport(id) {
    var row = document.querySelector('#seo-more-details-' + id);
    row.classList.toggle('show')
  }

  function reAudit(id) {
    crawlData(id)
  }

  function getFormDate(id) {
    const form = document.querySelector('#seo-description-form-' + id);
    const formData = new FormData(form);

    var formObject = {};
    formData.forEach((value, key) => {
      // Reflect.has in favor of: object.hasOwnProperty(key)
      if(!Reflect.has(formObject, key)){
        formObject[key] = value;
        return;
      }
      if(!Array.isArray(formObject[key])){
        formObject[key] = [formObject[key]];
      }
      formObject[key].push(value);
    });

    return formObject;
  }

  function updateMetaDescription(id) {
    console.log("update:", id);
    var formData = getFormDate(id);
    var url = "/wp-json/chat-gpt-seo/v1/update-meta-description/" + id;

    httpPost(url, false, formData, function (data) {
      console.log("success", data);
    }, function (data) {
      console.log("failed", data);
    });
  }

  function generateMetaDescription(id) {
    console.log("generate:", id);

    var generateButton = document.querySelector('#generate-button-'+id);
    var originalText = generateButton.innerHTML;
    generateButton.innerHTML="Generating";
    var formData = getFormDate(id);
    console.log(formData);
    var url = "/wp-json/chat-gpt-seo/v1/generate-meta-description/" + id;
    generateButton.setAttribute('disabled', 'disabled');
    httpPost(url, false, formData, function (data) {
      console.log("success", data);
      var metaDescription = data.response;
      var metaDescriptionField= document.querySelector('#seo-description-'+id);
      metaDescriptionField.value= metaDescription;
      generateButton.innerHTML = originalText;
      generateButton.removeAttribute('disabled');

    }, function (data) {
      console.log("failed", data);
      generateButton.innerHTML = originalText;
    });



  }


</script>

<h1>
    Seo audit
</h1>

<h2>Default keywords</h2>

<?php $list_of_keywords = [] ?>

<?php if (have_rows('keyword_list', 'option')): ?>
    <p>
    <ul class="soe-chat-gpt-keyword-list">
        <?php while (have_rows('keyword_list', 'option')): ?>
            <?php the_row(); ?>
            <?php $keyword = get_sub_field('keyword'); ?>
            <?php $keyword_variations_acf = get_sub_field('keyword_variations'); ?>
            <?php $keyword_variations = [] ?>
            <?php $keyword_variations_str = [] ?>
            <?php if ($keyword_variations_acf): ?>
                <?php foreach ($keyword_variations_acf as $keyword_variation): ?>
                    <?php $keyword_variations_str[] = $keyword_variation['keyword_variation']; ?>
                    <?php $keyword_variations[] = ['keyword' => $keyword_variation['keyword_variation'], 'count' => 0]; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php $full_list_of_keywords[] = ['keyword' => $keyword, 'variations' => $keyword_variations, 'count' => 0]; ?>
            <li><?= $keyword; ?>
                <?php if (is_array($keyword_variations) && count($keyword_variations) > 0): ?>
                    <ul>
                        <li><?= implode(",", $keyword_variations_str); ?></li>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
    <!--<pre>-->
    <!--    --><?php //print_r($full_list_of_keywords); ?>
    <!--</pre>-->
    </p>
<?php else: ?>
    <p>
        Please set the keywords
    </p>
<?php endif; ?>

<?php

$postSettings = [
    "Pages" => [
        'post_type' => 'page',
        'post_status' => ['publish', 'draft'],
        'numberposts' => -1,
        'suppress_filters' => false
    ],
    "Posts" => [
        'post_type' => 'post',
        'post_status' => ['publish', 'draft'],
        'numberposts' => -1,
        'suppress_filters' => false
    ]
];
?>


<?php foreach ($postSettings as $type => $settings): ?>
    <h2><?= $type ?></h2>
    <?php $items = get_posts($settings); ?>

    <?php if ($items): ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-header.php"); ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-content.php"); ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-footer.php"); ?>
    <?php else : ?>
        <pre>
            <?php print_r($items); ?>
        </pre>
    <?php endif; ?>

<?php endforeach; ?>

