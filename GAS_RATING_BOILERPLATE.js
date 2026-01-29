/*
  GOOGLE APPS SCRIPT BOILERPLATE - PENILAIAN LAYANAN (GAS RATING)
  --------------------------------------------------------------
  1. Create a Google Sheet.
  2. Open Extensions > Apps Script.
  3. Paste this code.
  4. Deploy as Web App (Manage Deployments > New > Type: Web App).
  5. Set "Execute as: Me" and "Who has access: Anyone".
  6. Copy the Web App URL and set it in your Laravel .env as GAS_RATING_URL.
*/

// CONFIGURATION - UPDATE THESE VALUES
const CONFIG = {
    SPREADSHEET_ID: '1TTA-EmLjMAeja-clG8fqWMfeeSzadKjR2xaH52fgv3s', // https://docs.google.com/spreadsheets/d/1TTA-EmLjMAeja-clG8fqWMfeeSzadKjR2xaH52fgv3s/edit?usp=sharing
    DRIVE_FOLDER_ID: '1H4WJ8xb-gDxtncv-0GURhNSOD5CXD9Yr', // https://drive.google.com/drive/folders/1H4WJ8xb-gDxtncv-0GURhNSOD5CXD9Yr?usp=sharing
    SKD_FORM_URL: 'https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec',
    SHEET_NAMES: {
        PETUGAS: 'Petugas',
        DB_PEGAWAI: 'db_pegawai',
        JADWAL: 'Jadwal Petugas',
        BUKU_TAMU: 'Buku Tamu',
        PERMINTAAN: 'Permintaan Data',
        PENILAIAN: 'Penilaian Petugas',
        RATING: 'Rating'
    }
};

function doGet(e) {
    // API Actions
    if (e.parameter.action === 'getPendingRatings') {
        return getPendingRatings();
    }

    // Default: Show Rating Form
    var t = HtmlService.createTemplateFromFile('index');
    t.token = e.parameter.token || "";
    return t.evaluate()
        .setTitle("Penilaian Layanan FO BPS")
        .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

function doPost(e) {
    var params = JSON.parse(e.postData.contents);
    var action = params.action || e.parameter.action;

    // API Actions
    if (action === 'markAsSynced') {
        var res = markAsSynced(params.rows);
        return ContentService.createTextOutput(JSON.stringify(res))
            .setMimeType(ContentService.MimeType.JSON);
    }

    // Front-end Form Submission
    return ContentService.createTextOutput(JSON.stringify(saveRating(params)))
        .setMimeType(ContentService.MimeType.JSON);
}

function saveRating(data) {

    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName("Ratings") || ss.insertSheet("Ratings");

    if (sheet.getLastRow() == 0) {
        sheet.appendRow(["Timestamp", "Token", "Keramahan", "Kecepatan", "Pengetahuan", "Keseluruhan", "Komentar", "Status Sinkron"]);
    }

    sheet.appendRow([
        new Date(),
        data.token,
        data.keramahan,
        data.kecepatan,
        data.pengetahuan,
        data.keseluruhan,
        data.komentar,
        "Pending"
    ]);

    return { success: true };
}

// API Endpoint for Laravel to fetch new ratings
function getPendingRatings() {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName("Ratings");
    if (!sheet) return JSON.stringify([]);

    var data = sheet.getDataRange().getValues();
    var headers = data.shift();
    var pending = [];

    for (var i = 0; i < data.length; i++) {
        if (data[i][7] === "Pending") { // Index 7 is Status Sinkron
            pending.push({
                row: i + 2,
                token: data[i][1],
                keramahan: data[i][2],
                kecepatan: data[i][3],
                pengetahuan: data[i][4],
                keseluruhan: data[i][5],
                komentar: data[i][6]
            });
        }
    }

    return ContentService.createTextOutput(JSON.stringify(pending))
        .setMimeType(ContentService.MimeType.JSON);
}

function markAsSynced(rows) {
    var ss = SpreadsheetApp.openById(CONFIG.SPREADSHEET_ID);
    var sheet = ss.getSheetByName("Ratings");
    rows.forEach(function (row) {
        sheet.getRange(row, 8).setValue("Synced"); // Index 8 is Status Sinkron
    });
    return { success: true };
}
