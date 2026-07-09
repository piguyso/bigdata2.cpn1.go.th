<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <style>
    {!! $fontFaceCss !!}
    @page { size: A4 landscape; margin: 0; }
    html, body {
      margin: 0;
      padding: 0;
      width: 297mm;
      height: 210mm;
      font-family: 'Sarabun', DejaVu Sans, sans-serif;
    }
    .certificate {
      position: relative;
      width: 297mm;
      height: 210mm;
      background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
      overflow: hidden;
    }
    .bg-image {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      {!! $backgroundStyle !!}
    }
    .content {
      position: absolute;
      inset: 0;
      text-align: center;
      color: #0f172a;
      z-index: 10;
    }
    .title-th { margin-top: 32mm; font-size: 26pt; font-weight: 700; }
    .title-en { margin-top: 1.5mm; font-size: 13pt; color: #334155; }
    .label { margin-top: 19mm; font-size: 14pt; color: #475569; }
    .name { margin-top: 4mm; font-size: 30pt; font-weight: 700; }
    .course-label { margin-top: 13mm; font-size: 14pt; color: #475569; }
    .course { margin-top: 4mm; font-size: 20pt; font-weight: 700; color: #1e293b; }
    .subtext { margin-top: 12mm; font-size: 12pt; color: #64748b; }
    .sign-row {
      position: absolute;
      left: 0;
      right: 0;
      bottom: 24mm;
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    .sign-col { display: table-cell; text-align: center; }
    .sign-line {
      width: 72mm;
      height: 0;
      border-top: 0.45mm solid #94a3b8;
      margin: 0 auto 4mm auto;
    }
    .sign-text { font-size: 12pt; color: #334155; }
  </style>
</head>
<body>
  <div class="certificate">
    <div class="bg-image"></div>
    <div class="content">
      <div class="title-th">เกียรติบัตร</div>
      <div class="title-en">Certificate of Completion</div>
      <div class="label">มอบให้แก่</div>
      <div class="name">{!! htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') !!}</div>
      <div class="course-label">เพื่อรับรองว่าได้ผ่านการเรียนหลักสูตร</div>
      <div class="course">{!! htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') !!}</div>
      <div class="subtext">ผ่านเกณฑ์คะแนนหลังเรียน และผ่านการตรวจงานครบทุกชิ้น</div>
    </div>
    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-line"></div>
        <div class="sign-text">ผู้รับรอง</div>
      </div>
      <div class="sign-col">
        <div class="sign-line"></div>
        <div class="sign-text">วันที่ {!! htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8') !!}</div>
      </div>
    </div>
  </div>
</body>
</html>
