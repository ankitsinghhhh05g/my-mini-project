<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    redirect('dashboard.php');
}
$page_title = 'Find your learning path';
require __DIR__ . '/includes/header.php';
?>
<section class="hero py-5 py-lg-6">
  <div class="container py-4 py-lg-5">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="text-uppercase small fw-bold text-info mb-3">Learn with direction</div>
        <h1 class="display-4 mb-3">Turn your interests into a focused career path.</h1>
        <p class="lead mb-4">Tell SkillPath about your background, take a quick assessment, and receive a practical shortlist of skills and courses made for you.</p>
        <div class="d-flex gap-3 flex-wrap"><a class="btn btn-light btn-lg px-4" href="register.php">Start for free</a><a class="btn btn-outline-light btn-lg px-4" href="#how-it-works">See how it works</a></div>
      </div>
      <div class="col-lg-5">
        <div class="hero-card p-4 p-lg-5">
          <div class="d-flex align-items-center gap-3 mb-4"><div class="rounded-circle bg-white text-primary fw-bold d-grid" style="width:48px;height:48px;place-items:center">01</div><div><div class="fw-bold">Build your profile</div><small class="text-white-50">Interests, skills, education, goals</small></div></div>
          <div class="d-flex align-items-center gap-3 mb-4"><div class="rounded-circle bg-white text-primary fw-bold d-grid" style="width:48px;height:48px;place-items:center">02</div><div><div class="fw-bold">Check your foundations</div><small class="text-white-50">A short, friendly skill assessment</small></div></div>
          <div class="d-flex align-items-center gap-3"><div class="rounded-circle bg-white text-primary fw-bold d-grid" style="width:48px;height:48px;place-items:center">03</div><div><div class="fw-bold">Follow your roadmap</div><small class="text-white-50">Save courses and track progress</small></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="how-it-works" class="container py-5 my-lg-4">
  <div class="text-center mb-5"><h2 class="section-title">A simple way to choose what to learn next</h2><p class="text-secondary mb-0">Less guessing, more meaningful progress.</p></div>
  <div class="row g-4">
    <div class="col-md-4"><div class="card card-hover h-100 p-4"><div class="feature-icon mb-3">👤</div><h5>Know your starting point</h5><p class="text-secondary mb-0">Capture your academic background, existing knowledge, interests, and career direction.</p></div></div>
    <div class="col-md-4"><div class="card card-hover h-100 p-4"><div class="feature-icon mb-3">🧠</div><h5>Identify growth areas</h5><p class="text-secondary mb-0">Use an assessment to find the technical and professional skills worth strengthening.</p></div></div>
    <div class="col-md-4"><div class="card card-hover h-100 p-4"><div class="feature-icon mb-3">🧭</div><h5>Learn with purpose</h5><p class="text-secondary mb-0">Receive rule-based course suggestions and keep track of every course you save.</p></div></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
