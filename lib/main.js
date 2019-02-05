function grecaptchaexecute() {
    grecaptcha.execute();
}
const set_id_api = (account_id) => {
    fetch('https://faucet.bitzeny.link/id_api.php?id=' + encodeURIComponent(account_id));
};
const get_id_api = () => {
    fetch('https://faucet.bitzeny.link/id_api.php', {
        'mode': 'cors',
        'credentials': 'include'
    }).then((response) => { return response.text(); }).then((text) => {
        if (text != "") {
            $('#account_id').val(text);
            $('#accent_id').addClass('is-focused');
            $("#account_ida").addClass('is-dirty');
        }
    });
};
var onGetZnyButton = (token) => {
    dialog.showModal();
    document.cookie = 'account_id=' + encodeURIComponent($('#account_id').val());
    set_id_api($('#account_id').val());
    $.ajax({
        url: './giveme.php',
        type: 'POST',
        dataType: 'json',
        data: {
            'g_token': token,
            'account_id': $('#account_id').val()
        }
    }).done((j) => {
        history.pushState(null, null, './');
        if (j['result']) {
            $('#dcc').fadeIn(300);
            $('#dl_ps').fadeOut(300);
            document.getElementById('progresstext').innerText = j['amount'] + "ZNYが送られました!";
        } else {
            $('#dl_ps').fadeOut(300);
            document.getElementById('progresstext').innerText = j['message'];
        }
        console.log("success");
    }).fail(() => {
        alert("通信エラーが発生しました。");
        console.log("error");
    });

};
const get_cookie = () => {
    let r = [];
    if (document.cookie == '') return r;
    let cks = document.cookie.split('; ');
    for (var i = 0; i < cks.length; i++) {
        let v = cks[i].split('=');
        r[v[0]] = decodeURIComponent(v[1]);
    };
    return r;
};
var dialog;
$(() => {
    fetch('./ad_api.php?v=balance').then((response) => { return response.json(); }).then((json) => {
        if (json['result']) {
            $('.faucetbalance').text(json['balance']);
        }
    });
    fetch('./ad_api.php?v=messages').then((response) => { return response.json(); }).then((json) => {
        if (json['result']) {
            document.getElementById('rewardmsg').innerText = json['reward'];
            document.getElementById('rewardiv').innerText = json['reward_interval'];
        }
    });
    let cookies = get_cookie();
    if ('' != (cookies['account_id'] || '')) {
        $('#account_id').val(cookies['account_id']);
        $('#accent_id').addClass('is-focused');
        $("#account_ida").addClass('is-dirty');
    } else {
        get_id_api();
    }
    $('#form-front').on('submit', (ev) => {
        ev.preventDefault();
        dialog.showModal();
    });
    $('#get_zny_button').on('click', () => {
        dialog.close();
        grecaptchaexecute();
        $('.confirm-btn').fadeOut(500);
        $('#dl_ps').fadeIn(300);
    });
    $('.close').on('click', () => {
        dialog.close();
    });
    dialog = document.querySelector('dialog');
    var showDialogButton = document.querySelector('#show-dialog');
    if (!dialog.showModal) {
        dialogPolyfill.registerDialog(dialog);
    }

    dialog.querySelector('.close').addEventListener('click', () => { dialog.close(); });

});