<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminTemplateController
{
    /**
     * List templates.
     */
    public function index(): void
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->query('SELECT * FROM templates ORDER BY type');
        $templates = $stmt->fetchAll();
        view('admin/templates', ['templates' => $templates]);
    }

    /**
     * Edit template or create new.
     */
    public function edit(): void
    {
        $pdo = DB::getConnection();
        $id = intval($_GET['id'] ?? 0);
        $template = null;
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM templates WHERE id=?');
            $stmt->execute([$id]);
            $template = $stmt->fetch();
            if (!$template) {
                flash('error', '模板不存在');
                redirect('/admin.php?r=templates');
                return;
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type    = trim($_POST['type'] ?? '');
            $name    = trim($_POST['name'] ?? '');
            $content = trim($_POST['content'] ?? '');
            if ($type === '' || $name === '' || $content === '') {
                flash('error', '所有字段均为必填');
                view('admin/template_edit', ['template' => $template]);
                return;
            }
            if ($id) {
                $stmt = $pdo->prepare('UPDATE templates SET type=?, name=?, content=?, updated_at=NOW() WHERE id=?');
                $stmt->execute([$type, $name, $content, $id]);
                log_admin_action('edit_template', '编辑模板 ID: ' . $id);
                flash('success', '模板更新成功');
            } else {
                $stmt = $pdo->prepare('INSERT INTO templates (type, name, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
                $stmt->execute([$type, $name, $content]);
                $newId = $pdo->lastInsertId();
                log_admin_action('create_template', '创建模板 ID: ' . $newId);
                flash('success', '模板创建成功');
            }
            redirect('/admin.php?r=templates');
            return;
        }
        view('admin/template_edit', ['template' => $template]);
    }

    /**
     * Preview a template content.
     */
    public function preview(): void
    {
        $pdo = DB::getConnection();
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM templates WHERE id = ?');
        $stmt->execute([$id]);
        $template = $stmt->fetch();
        if (!$template) {
            flash('error', '模板不存在');
            redirect('/admin.php?r=templates');
            return;
        }
        view('admin/template_preview', ['template' => $template]);
    }
}