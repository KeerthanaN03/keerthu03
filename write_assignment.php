<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['assignment_id'])) {
    echo "❌ No assignment specified.";
    exit;
}

$assignment_id = intval($_GET['assignment_id']);

$sql = "SELECT a.assignment_id, a.assignment, a.teacher_id, a.deadline, a.course, a.year, a.total_marks, 
               t.teacher_name, a.student_id, a.student_name
        FROM assignments a
        JOIN teachers t ON a.teacher_id = t.teacher_id
        WHERE a.assignment_id = ? AND a.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignment_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();

if (!$assignment) {
    echo "❌ Assignment not found or access denied.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Write Assignment</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .tabs button { margin-right: 10px; padding: 8px 14px; cursor: pointer; }
        .toolbar { display: none; margin: 10px 0; }
        .toolbar.active { display: block; }
        #editor {
            border: 1px solid #ccc;
            min-height: 500px;
            padding: 10px;
            position: relative;
            overflow: hidden;
        }
        .shape {
            position: absolute;
            border: 1px solid black;
            background-color: transparent;
            resize: both;
            overflow: hidden;
            user-select: none;
            padding: 10px;
            cursor: move;
        }
        .circle { border-radius: 50%; width: 100px; height: 100px; }
        .square { width: 100px; height: 100px; }
        .rectangle { width: 150px; height: 100px; }
        .triangle {
            width: 0; height: 0;
            border-left: 50px solid transparent;
            border-right: 50px solid transparent;
            border-bottom: 100px solid black;
            background: none;
        }
        .diamond {
            width: 100px; height: 100px;
            transform: rotate(45deg);
        }
        .arrow {
            width: 0;
            height: 0;
            border-top: 20px solid transparent;
            border-bottom: 20px solid transparent;
            border-left: 40px solid black;
        }
        .line {
            position: absolute;
            height: 2px;
            background-color: black;
            z-index: 10;
        }
    </style>
</head>
<body>

<h2>Write Your Assignment</h2>
<p><strong>Teacher:</strong> <?= htmlspecialchars($assignment['teacher_name']) ?></p>
<p><strong>Total Marks:</strong> <?= htmlspecialchars($assignment['total_marks']) ?></p>
<p><strong>Deadline:</strong> <?= htmlspecialchars($assignment['deadline']) ?></p>
<p><strong>Question:</strong> <?= nl2br(htmlspecialchars($assignment['assignment'])) ?></p>

<div class="tabs">
    <button onclick="switchTab('home')">Home</button>
    <button onclick="switchTab('insert')">Insert</button>
    <button onclick="switchTab('design')">Design</button>
</div>

<div id="home" class="toolbar active">
    <button onclick="execCmd('undo')">Undo</button>
    <button onclick="execCmd('redo')">Redo</button>
    <button onclick="execCmd('bold')">Bold</button>
    <button onclick="execCmd('italic')">Italic</button>
    <button onclick="refreshEditor()">Refresh</button>
    <button onclick="exitShapeMode()">✏️ Pen (Write Mode)</button>
</div>

<div id="insert" class="toolbar">
    <button onclick="insertTable()">Insert Table</button>
    <button onclick="setShape('circle')">Circle</button>
    <button onclick="setShape('square')">Square</button>
    <button onclick="setShape('rectangle')">Rectangle</button>
    <button onclick="setShape('triangle')">Triangle</button>
    <button onclick="setShape('diamond')">Diamond</button>
    <button onclick="setShape('arrow')">Arrow</button>
    <button onclick="startLine()">Line</button>
    <!-- New Math and Upload Buttons -->
    <button onclick="insertSymbol('√')">√ Root</button>
    <button onclick="insertSymbol('²')">x²</button>
    <button onclick="execCmd('superscript')">Superscript</button>
    <button onclick="execCmd('subscript')">Subscript</button>
    <input type="file" id="imgUpload" accept="image/*" style="display:none;" onchange="uploadImage(event)">
    <button onclick="document.getElementById('imgUpload').click()">Upload Image</button>
    <input type="file" id="fileUpload" style="display:none;" onchange="uploadFile(event)">
    <button onclick="document.getElementById('fileUpload').click()">Attach File</button>
</div>

<div id="design" class="toolbar">
    <button style="color:red;" onclick="execCmd('foreColor', 'red')">Red</button>
    <button style="color:blue;" onclick="execCmd('foreColor', 'blue')">Blue</button>
    <button style="color:green;" onclick="execCmd('foreColor', 'green')">Green</button>
    <button style="color:black;" onclick="execCmd('foreColor', 'black')">Black</button>
</div>

<div id="editor" contenteditable="true"></div>

<form method="POST" action="submit_assignment.php" onsubmit="return saveContent()">
    <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">
    <textarea name="response" id="response" style="display:none;"></textarea>
    <br>
    <button type="submit">Submit Assignment</button>
</form>

<script>
let currentShape = null;
let isDrawingLine = false;
let lineStart = null;
let tempLine = null;

function switchTab(tab) {
    document.querySelectorAll('.toolbar').forEach(t => t.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
}

function execCmd(cmd, val = null) {
    document.execCommand(cmd, false, val);
}

function refreshEditor() {
    if (confirm("Clear the editor?")) {
        document.getElementById("editor").innerHTML = '';
    }
}

function insertTable() {
    let rows = parseInt(prompt("Rows:", 2));
    let cols = parseInt(prompt("Columns:", 2));
    let table = "<table border='1' style='width:100%; border-collapse:collapse;'>";
    for (let i = 0; i < rows; i++) {
        table += "<tr>";
        for (let j = 0; j < cols; j++) {
            table += "<td>&nbsp;</td>";
        }
        table += "</tr>";
    }
    table += "</table>";
    document.getElementById("editor").innerHTML += table;
}

function setShape(shape) {
    currentShape = shape;
    isDrawingLine = false;
    editor.setAttribute("contenteditable", "false");
}

function startLine() {
    currentShape = null;
    isDrawingLine = true;
    editor.setAttribute("contenteditable", "false");
}

function exitShapeMode() {
    currentShape = null;
    isDrawingLine = false;
    editor.setAttribute("contenteditable", "true");
}

function insertSymbol(symbol) {
    const span = document.createElement('span');
    span.textContent = symbol;
    const sel = window.getSelection();
    if (sel.rangeCount) {
        const range = sel.getRangeAt(0);
        range.deleteContents();
        range.insertNode(span);
        range.collapse(false);
    }
}

function uploadImage(event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = "200px";
            img.style.maxHeight = "200px";
            img.style.display = "block";
            editor.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}

function uploadFile(event) {
    const file = event.target.files[0];
    if (file) {
        const fileName = file.name;
        const url = URL.createObjectURL(file);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        link.textContent = "📎 " + fileName;
        link.target = "_blank";
        editor.appendChild(link);
        editor.appendChild(document.createElement("br"));
    }
}

const editor = document.getElementById("editor");

editor.addEventListener("mousedown", function (e) {
    const rect = editor.getBoundingClientRect();
    const x = e.clientX - rect.left + editor.scrollLeft;
    const y = e.clientY - rect.top + editor.scrollTop;

    if (isDrawingLine) {
        lineStart = { x, y };
        tempLine = document.createElement("div");
        tempLine.className = "line";
        tempLine.style.left = x + "px";
        tempLine.style.top = y + "px";
        editor.appendChild(tempLine);
        editor.addEventListener("mousemove", drawLinePreview);
        editor.addEventListener("mouseup", finalizeLine);
    } else if (currentShape) {
        const div = document.createElement("div");
        div.className = "shape " + currentShape;
        div.contentEditable = false;
        div.style.left = x + "px";
        div.style.top = y + "px";
        editor.appendChild(div);
        makeDraggable(div);
        currentShape = null;
        editor.setAttribute("contenteditable", "true");
    }
});

function drawLinePreview(e) {
    const rect = editor.getBoundingClientRect();
    const x2 = e.clientX - rect.left + editor.scrollLeft;
    const y2 = e.clientY - rect.top + editor.scrollTop;

    const dx = x2 - lineStart.x;
    const dy = y2 - lineStart.y;
    const length = Math.sqrt(dx * dx + dy * dy);
    const angle = Math.atan2(dy, dx) * (180 / Math.PI);

    tempLine.style.width = length + "px";
    tempLine.style.transform = `rotate(${angle}deg)`;
    tempLine.style.transformOrigin = "0 0";
}

function finalizeLine(e) {
    editor.removeEventListener("mousemove", drawLinePreview);
    editor.removeEventListener("mouseup", finalizeLine);
    tempLine = null;
    lineStart = null;
    isDrawingLine = false;
    editor.setAttribute("contenteditable", "true");
}

function makeDraggable(el) {
    let isDragging = false;
    let offsetX, offsetY;

    el.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.offsetX;
        offsetY = e.offsetY;
        el.style.zIndex = "1000";
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        const rect = editor.getBoundingClientRect();
        el.style.left = (e.clientX - rect.left - offsetX + editor.scrollLeft) + "px";
        el.style.top = (e.clientY - rect.top - offsetY + editor.scrollTop) + "px";
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
    });
}

function saveContent() {
    document.getElementById('response').value = document.getElementById('editor').innerHTML;
    return true;
}
</script>
</body>
</html>
