/*
  GOOGLE APPS SCRIPT BOILERPLATE - PENILAIAN LAYANAN (GAS RATING)
  --------------------------------------------------------------
  Logika ini menangani:
  1. Sinkronisasi Petugas (ID & Nama) dari Laravel.
  2. Pencarian Nama Petugas berdasarkan ID saat form rating dibuka.
  3. Pengambilan data rating oleh Laravel (Sync Ratings).
*/

// CONFIGURATION
const CONFIG = {
    SPREADSHEET_ID: '1TTA-EmLjMAeja-clG8fqWMfeeSzadKjR2xaH52fgv3s', // Sesuaikan dengan ID Spreadsheet Anda
    DRIVE_FOLDER_ID: '1H4WJ8xb-gDxtncv-0GURhNSOD5CXD9Yr', // Sesuaikan dengan ID Folder Google Drive Anda
    SHEET_NAMES: {
        RATINGS: 'Ratings',
        MASTER_PETUGAS: 'Master_Petugas'
    }
};

function doGet(e) {
    var token = e.parameter.token || "";
    var officer_id = e.parameter.officer_id;
    var officer_name = "Petugas"; // Default
    var officer_photo = null;

    // Jika ada officer_id, cari namanya & foto di Master_Petugas
    if (officer_id) {
        var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
        var sheetPetugas = ss.getSheetByName(CONFIG.SHEET_NAMES.MASTER_PETUGAS) || ss.insertSheet(CONFIG.SHEET_NAMES.MASTER_PETUGAS);
        var dataPetugas = sheetPetugas.getDataRange().getValues();

        for (var i = 1; i < dataPetugas.length; i++) {
            if (dataPetugas[i][0].toString() === officer_id.toString()) {
                officer_name = dataPetugas[i][1];
                officer_photo = dataPetugas[i][2] || null;
                break;
            }
        }
    }

    // API Action: Get Pending Ratings (Digunakan oleh Laravel RatingSyncController)
    if (e.parameter.action === 'getPendingRatings') {
        return getPendingRatings();
    }

    // Default: Tampilkan form rating
    var t = HtmlService.createTemplateFromFile('GAS_INDEX_FORM');
    t.token = token;
    t.officer_name = officer_name;
    t.officer_photo = officer_photo;

    return t.evaluate()
        .setTitle("Penilaian Layanan")
        .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

function doPost(e) {
    var params = JSON.parse(e.postData.contents);
    var action = params.action || e.parameter.action;

    // 1. Sync Petugas Individual
    if (action === 'syncOfficer') {
        upsertPetugas(params.officer_id, params.officer_name, params.photo_base64, params.photo_name);
        return ContentService.createTextOutput(JSON.stringify({ success: true })).setMimeType(ContentService.MimeType.JSON);
    }

    // 2. Sync Petugas Batch
    if (action === 'syncOfficersBatch') {
        var dataList = params.data;
        dataList.forEach(function (item) {
            upsertPetugas(item.officer_id, item.officer_name, item.photo_base64, item.photo_name);
        });
        return ContentService.createTextOutput(JSON.stringify({ success: true, count: dataList.length })).setMimeType(ContentService.MimeType.JSON);
    }

    // 3. Mark Ratings as Synced (dari Laravel)
    if (action === 'markAsSynced') {
        var res = markAsSynced(params.rows);
        return ContentService.createTextOutput(JSON.stringify(res)).setMimeType(ContentService.MimeType.JSON);
    }

    // 4. Submit Form Rating (dari User/Pengunjung)
    return ContentService.createTextOutput(JSON.stringify(saveRating(params))).setMimeType(ContentService.MimeType.JSON);
}

function upsertPetugas(id, name, photoBase64, photoName) {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName(CONFIG.SHEET_NAMES.MASTER_PETUGAS) || ss.insertSheet(CONFIG.SHEET_NAMES.MASTER_PETUGAS);

    if (sheet.getLastRow() === 0) {
        sheet.appendRow(["officer_id", "officer_name", "officer_photo_url"]);
    }

    var photoUrl = "";
    if (photoBase64 && photoName) {
        photoUrl = uploadToDrive(photoBase64, photoName, id);
    }

    var data = sheet.getDataRange().getValues();
    var found = false;
    for (var i = 1; i < data.length; i++) {
        if (data[i][0].toString() === id.toString()) {
            sheet.getRange(i + 1, 2).setValue(name);
            if (photoUrl) {
                sheet.getRange(i + 1, 3).setValue(photoUrl);
            }
            found = true;
            break;
        }
    }
    if (!found) {
        sheet.appendRow([id, name, photoUrl]);
    }
}

function uploadToDrive(base64Data, fileName, officerId) {
    try {
        var folder = DriveApp.getFolderById(CONFIG.DRIVE_FOLDER_ID);
        var bytes = Utilities.base64Decode(base64Data);
        var blob = Utilities.newBlob(bytes, null, fileName);

        // Hapus foto lama jika ada (opsional, untuk menghemat space)
        var files = folder.getFilesByName(fileName);
        while (files.hasNext()) {
            files.next().setTrashed(true);
        }

        var file = folder.createFile(blob);
        file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

        // Return URL thumbnail / direct link yang bisa di embed
        return "https://drive.google.com/uc?export=view&id=" + file.getId();
    } catch (e) {
        Logger.log("Upload error: " + e.toString());
        return "";
    }
}

function saveRating(data) {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName(CONFIG.SHEET_NAMES.RATINGS) || ss.insertSheet(CONFIG.SHEET_NAMES.RATINGS);

    if (sheet.getLastRow() == 0) {
        sheet.appendRow(["Timestamp", "Token", "Keramahan", "Kecepatan", "Pengetahuan", "Keseluruhan", "Komentar", "Status Sinkron"]);
    }

    sheet.appendRow([
        new Date(),
        data.token,
        data.keramahan || 0,
        data.kecepatan || 0,
        data.pengetahuan || 0,
        data.rating || data.keseluruhan || 0,
        data.comment || data.komentar || "",
        "Pending"
    ]);

    return { success: true };
}

function getPendingRatings() {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName(CONFIG.SHEET_NAMES.RATINGS);
    if (!sheet) return ContentService.createTextOutput(JSON.stringify([])).setMimeType(ContentService.MimeType.JSON);

    var data = sheet.getDataRange().getValues();
    var pending = [];

    for (var i = 1; i < data.length; i++) {
        if (data[i][7] === "Pending") {
            pending.push({
                row: i + 1,
                token: data[i][1],
                keramahan: data[i][2],
                kecepatan: data[i][3],
                pengetahuan: data[i][4],
                keseluruhan: data[i][5],
                komentar: data[i][6]
            });
        }
    }

    return ContentService.createTextOutput(JSON.stringify(pending)).setMimeType(ContentService.MimeType.JSON);
}

function markAsSynced(rows) {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName(CONFIG.SHEET_NAMES.RATINGS);
    rows.forEach(function (row) {
        sheet.getRange(row, 8).setValue("Synced");
    });
    return { success: true };
}
