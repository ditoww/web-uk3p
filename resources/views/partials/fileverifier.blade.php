<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
function getFileSignature(file, length = 4) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onloadend = function(e) {
      if (e.target.readyState === FileReader.DONE) {
        const arr = new Uint8Array(e.target.result).subarray(0, length);
        const hex = Array.from(arr)
          .map(b => b.toString(16).padStart(2, "0"))
          .join("");
        resolve(hex);
      }
    };

    reader.onerror = reject;

    reader.readAsArrayBuffer(file.slice(0, length));
  });
}

async function isWebP(file) {
  const bytes = await getFileSignature(file, 12);
  const riff = String.fromCharCode(...bytes.slice(0, 4));
  const webp = String.fromCharCode(...bytes.slice(8, 12));
  return riff === "RIFF" && webp === "WEBP";
}

async function isDocx(file) {
  const sig = await getFileSignature(file);
  if (sig !== "504b0304") return false;

  const zip = await JSZip.loadAsync(file);
  return zip.files["[Content_Types].xml"] && zip.files["word/document.xml"] && zip.files["_rels/.rels"];
}
async function isXlsx(file) {
  const sig = await getFileSignature(file);
  if (sig !== "504b0304") return false;

  const zip = await JSZip.loadAsync(file);
  return zip.files["[Content_Types].xml"] && zip.files["xl/styles.xml"] && zip.files["_rels/.rels"] && zip.files['xl/workbook.xml'], zip.files['xl/_rels/workbook.xml.rels'];
}
</script>