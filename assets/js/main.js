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
          console.log(e);
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


function toggleStatusBar(show) {
  var section = document.querySelector('.cgs--status');
  if (section) {
    if (show) {
      section.classList.remove('cgs--hide');
    } else {
      section.classList.add('cgs--hide');
    }

  }
}

function crawlData(passId) {
  var to_do = document.querySelectorAll('.chat-gpt-seo-check-post').length;
  var checked_count = document.querySelectorAll('.chat-gpt-seo-report-done').length;
  var total = to_do + checked_count;
  var percents = Math.ceil(checked_count / total * 100);

  if (percents === 100) {
    toggleStatusBar(false)
  } else {
    toggleStatusBar(true)
  }

  var statusElement = document.querySelector('.chat-gpt-seo-status');
  statusElement.style.width = percents + "%";

  var element = document.querySelector('.chat-gpt-seo-check-post');
  var id = passId;
  if (element) {
    id = element.getAttribute('data-id');
    element.classList.remove('chat-gpt-seo-check-post');
    element.classList.add('chat-gpt-seo-check-checking-' + id);

  }
  var force = false;
  if (passId) {
    force = true;
  }

  var url = "/wp-json/chat-gpt-seo/v1/audit-item/" + id;
  if (force) {
    url = "/wp-json/chat-gpt-seo/v1/force-audit-item/" + id;
  }

  var status = document.querySelector('#penalty-' + id);
  if (status) {
    status.innerHTML = "...auditing...";
  }

  httpGet(url, null, (data) => {
    var element = document.querySelector('.chat-gpt-seo-check-checking-' + data.id);
    if (element) {
      element.classList.remove('chat-gpt-seo-check-checking-' + data.id);
      element.classList.add("chat-gpt-seo-report-done");
    }
    status.innerHTML = "...done...";


    var firstRow = document.querySelector('#seo-summary-' + data.id);
    if (firstRow) {
      firstRow.innerHTML = data.html.first_row_html;
    }
    element = document.querySelector('.chat-gpt-seo-check-post');
    if (element && !force) {
      crawlData();
    }
  });
}

setTimeout(function () {
  crawlData();
}, 3000)


function toggleBackground(show) {
  var background = document.querySelector('.cgs-modal-bg');
  if (show){
    background.classList.add('cgs--show');
  } else {
    background.classList.remove('cgs--show');
  }
}

function expandReport(id) {
  var element = document.querySelector('#cgs-more-details-' + id);
  if (element){
    element.classList.toggle('cgs--show');
    if (element.classList.contains('.cgs--show')){
      toggleBackground(true);
    } else {
      toggleBackground(true);
    }
  }
}

function reAudit(id) {
  crawlData(id);
}

function getFormDate(id) {
  const form = document.querySelector('#seo-description-form-' + id);
  const formData = new FormData(form);

  var formObject = {};
  formData.forEach((value, key) => {
    // Reflect.has in favor of: object.hasOwnProperty(key)
    if (!Reflect.has(formObject, key)) {
      formObject[key] = value;
      return;
    }
    if (!Array.isArray(formObject[key])) {
      formObject[key] = [formObject[key]];
    }
    formObject[key].push(value);
  });

  return formObject;
}

function disableTextField(id) {
  var textField = document.querySelector('#seo-description-' + id);
  textField.setAttribute('readonly', 'readonly');
  textField.setAttribute('disabled', 'disabled');
}

function enableTextField(id) {
  var textField = document.querySelector('#seo-description-' + id);
  textField.removeAttribute('readonly');
  textField.removeAttribute('disabled');
}

function updateMetaDescription(id) {
  var formData = getFormDate(id);
  var url = "/wp-json/chat-gpt-seo/v1/update-meta-description/" + id;

  disableTextField(id);

  var button = document.querySelector('#update-meta-description-button-'+id);
  button.innerHTML = "Updating..."
  button.setAttribute('disabled','disabled');

  function resetButtonText(id){
    var button = document.querySelector('#update-meta-description-button-'+id);
    button.innerHTML = button.getAttribute('data-original-button-text');
    button.removeAttribute('disabled');
  }

  httpPost(url, false, formData, function (data) {
    var button = document.querySelector('#update-meta-description-button-'+id);
    enableTextField(id);
    button.innerHTML = "Updated";
    setTimeout(function(){
    resetButtonText(id);
    },1000)

  }, function (data) {
    enableTextField(id);
    button.innerHTML = "Failed :(";
    resetButtonText(id);
  });
}

function generateMetaDescription(id) {
  var generateButton = document.querySelector('#generate-button-' + id);
  var originalText = generateButton.innerHTML;
  generateButton.innerHTML = "Generating...";
  var formData = getFormDate(id);
  var url = "/wp-json/chat-gpt-seo/v1/generate-meta-description/" + id;
  generateButton.setAttribute('disabled', 'disabled');

  disableTextField(id);

  httpPost(url, false, formData, function (data) {
    var metaDescription = data.response;
    var metaDescriptionField = document.querySelector('#seo-description-' + id);
    metaDescriptionField.value = metaDescription;
    generateButton.innerHTML = originalText;
    generateButton.removeAttribute('disabled');
    enableTextField(id)
    generateButton.innerHTML='Generated!';
    setTimeout(function (){
      generateButton.innerHTML = originalText;
    },1000)

  }, function (data) {
    generateButton.innerHTML='Failed ;(';
    setTimeout(function (){
      generateButton.innerHTML = originalText;
    },1000)

  });
}

document.querySelector('.cgs-modal-bg').addEventListener('click', function(){
  toggleBackground(false);
  var modals= document.querySelectorAll('.cgs--show');
  modals.forEach(function(item){
    item.classList.remove('cgs--show');
  })
});


jQuery(document).ready(function () {
  jQuery('.chat-gpt-seo-table').DataTable(
   {
     "columnDefs": [
       { "orderable": false, "targets": [3,4,5,6] }
     ]
   }
  );
});
