
document.addEventListener('DOMContentLoaded', function () {
    handleApiVersionChange();
    const apiradios = document.querySelectorAll('input[name="lskypro_api_version"]');
    const opensourceRadios = document.querySelectorAll('input[name="lskypro_opensource"]');
    apiradios.forEach(radio => {
        radio.addEventListener('change', handleApiVersionChange);
    });
    opensourceRadios.forEach(radio => {
        radio.addEventListener('change', handleApiVersionChange);
    });

    function handleApiVersionChange() {
        const selected = document.querySelector('input[name="lskypro_api_version"]:checked');
        const selectedopensource = document.querySelector('input[name="lskypro_opensource"]:checked');
        if (!selected) return;
        if (!selectedopensource) return;

        const version = selected.value;
        const opensource = selectedopensource.value;
        const opensourceFields = document.querySelectorAll('.opensource');
        const v1Fields = document.querySelectorAll('.api-v1');
        const v2Fields = document.querySelectorAll('.api-v2');
        const commonFields = document.querySelectorAll('#v1orv2');

        v2Fields.forEach(el => el.style.display = (version === 'v2') ? '' : 'none');
        v1Fields.forEach(el => el.style.display = (version === 'v1') ? '' : 'none');
        opensourceFields.forEach(el => el.style.display = (opensource === '1' && version === 'v1') ? '' : 'none');
        commonFields.forEach(el => el.readOnly = (version === 'v2' || opensource === '0') ? false : true);
        if (version === 'v2'){
            fetchV2Data();
        }
    }
    document.getElementById('update-token-btn').addEventListener('click', async function () {
    const api = document.querySelector('input[name="lskypro_api_url"]').value;
    const username = document.querySelector('input[name="lskypro_username"]').value;
    const password = document.querySelector('input[name="lskypro_password"]').value;

    if (!api || !username || !password) {
        alert('请填写 API、用户名和密码');
        return;
    }
    try {
        const res = await fetch(window.location.origin + '/action/lskypro-ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'update_token',
                api: api,
                username: username,
                password: password
            })
        });
        const data = await res.json();
        if (data.status) {
            document.querySelector('input[name="lskypro_token"]').value = data.token;
            alert('Token 获取成功！');
        } else {
            alert('获取失败：' + data.msg);
        }
    } catch (err) {
        alert('请求失败：' + err.message);
    }
    
    });
    async function fetchV2Data() {
    const api = document.querySelector('input[name="lskypro_api_url"]').value;
    const token = document.querySelector('input[name="lskypro_token"]').value;
    const albumSelect = document.getElementById('lskypro_album_id');
    const storageSelect = document.getElementById('lskypro_storage_id');
    albumSelect.disabled = true;
    storageSelect.disabled = true;
    try {
        const res = await fetch(window.location.origin + '/action/lskypro-ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: "getAlbums",
                api: api,
                token: token
            })
        });
        const data = await res.json();
        albumSelect.innerHTML = '<option value="">请选择相册</option>';
        storageSelect.innerHTML = '<option value="">请选择存储策略</option>';
        if (data.status) {
            data.albums.forEach(item => {
                const selected = (item.id == savedAlbumId) ? 'selected' : '';
                albumSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.name}</option>`;
            });
            data.storages.forEach(item => {
                const selected = (item.id == savedStorageId) ? 'selected' : '';
                storageSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.name}</option>`;
            });
        } else {
            albumSelect.innerHTML = '<option value="">无法加载相册</option>';
            storageSelect.innerHTML = '<option value="">无法加载存储策略</option>';
        }
    } catch (error) {
        albumSelect.innerHTML = '<option value="">加载失败</option>';
        storageSelect.innerHTML = '<option value="">加载失败</option>';
    } finally {
        albumSelect.disabled = false;
        storageSelect.disabled = false;
    }
}
});

