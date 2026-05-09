(function () {
    'use strict';

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const telRe = /^[0-9]{8}$/;
    const dateRe = /^\d{4}-\d{2}-\d{2}$/;
    const PASSWORD_SPECIAL_CHARSET = '!@#$%^&*+-=:,.<>?[]{}()|/~';

    function passwordHasSpecialChar(value) {
        for (let i = 0; i < value.length; i++) {
            if (PASSWORD_SPECIAL_CHARSET.indexOf(value[i]) !== -1) {
                return true;
            }
        }
        return false;
    }

    /** @returns {{checks: Record<string,boolean>, met: number, meetsPolicy: boolean, hasSpace: boolean}} */
    function analyzePasswordSignup(value) {
        const trimmed = typeof value === 'string' ? value : '';
        const hasSpace = /\s/.test(trimmed);
        const checks = {
            longueur: trimmed.length >= 10,
            majuscule: /[A-Z]/.test(trimmed),
            minuscule: /[a-z]/.test(trimmed),
            chiffre: /[0-9]/.test(trimmed),
            special: passwordHasSpecialChar(trimmed),
        };
        const met = Object.keys(checks).filter(function (k) {
            return checks[k];
        }).length;
        const meetsPolicy = trimmed.length > 0 && !hasSpace && Object.keys(checks).every(function (k) {
            return checks[k];
        });
        return { checks: checks, met: met, meetsPolicy: meetsPolicy, hasSpace: hasSpace };
    }

    /**
     * 0=vide … 5=fort (tous les criteres de la plateforme).
     */
    function passwordStrengthLevel(value) {
        const v = typeof value === 'string' ? value : '';
        if (v === '') {
            return 0;
        }
        const a = analyzePasswordSignup(v);
        if (a.hasSpace) {
            return Math.max(1, Math.min(a.met, 4));
        }
        if (a.met <= 1) {
            return 1;
        }
        if (a.met === 2) {
            return 2;
        }
        if (a.met === 3) {
            return 3;
        }
        if (a.met === 4) {
            return 4;
        }
        return 5;
    }

    function renderSignupPasswordStrength() {
        const input = document.getElementById('signupPassword');
        if (!input) {
            return;
        }

        let box = document.getElementById('signupPasswordStrength');
        if (!box) {
            box = document.createElement('div');
            box.id = 'signupPasswordStrength';
        }

        // Ensure the indicator is positioned BEFORE the input in the DOM (above the field)
        if (input.parentNode && box.nextSibling !== input) {
            input.parentNode.insertBefore(box, input);
        }

        const value = input.value;
        const a = analyzePasswordSignup(value);

        if (value === '') {
            box.innerHTML = '';
            box.style.display = 'none';
            box.className = 'password-strength';
            return;
        }

        box.style.display = 'block';

        let label, color, bg, border;
        if (a.hasSpace) {
            label = 'Espaces non autorisés dans le mot de passe';
            color = '#c0392b';
            bg = '#fdecea';
            border = '1px solid #f5c6cb';
        } else if (a.met <= 2) {
            label = 'Faible';
            color = '#c0392b';
            bg = '#fdecea';
            border = '1px solid #f5c6cb';
        } else if (a.met <= 4) {
            label = 'Moyen';
            color = '#e67e22';
            bg = '#fff3e0';
            border = '1px solid #ffe0b2';
        } else {
            label = 'Fort';
            color = '#27ae60';
            bg = '#eafaf1';
            border = '1px solid #c3e6cb';
        }

        box.className = 'password-strength';
        box.style.cssText = 'display:block;padding:6px 10px;border-radius:6px;font-size:13px;margin-bottom:6px;' +
            'color:' + color + ';background:' + bg + ';border:' + border + ';';

        const criteriaLabels = {
            longueur: 'au moins 10 caractères',
            majuscule: 'une majuscule',
            minuscule: 'une minuscule',
            chiffre: 'un chiffre',
            special: 'un symbole (ex: @ # $ % &)',
        };
        const missing = Object.keys(a.checks).filter(function (k) { return !a.checks[k]; });
        const missingText = missing.length > 0
            ? '<div style="margin-top:3px;font-size:12px;">Manque : ' + missing.map(function (k) { return criteriaLabels[k]; }).join(', ') + '</div>'
            : '';

        box.innerHTML = '<strong>' + label + '</strong>' + missingText;
    }

    /** @returns {string} message d erreur ou chaine vide */
    function validateSignupPasswordPolicy(pw) {
        if (/\s/.test(pw)) {
            return 'Le mot de passe ne doit pas contenir d\'espaces.';
        }
        if (pw.length < 10) {
            return 'Le mot de passe doit contenir au moins 10 caracteres.';
        }
        if (!/[A-Z]/.test(pw)) {
            return 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!/[a-z]/.test(pw)) {
            return 'Le mot de passe doit contenir au moins une minuscule.';
        }
        if (!/[0-9]/.test(pw)) {
            return 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!passwordHasSpecialChar(pw)) {
            return 'Le mot de passe doit contenir au moins un caractere special (exemple: @ # $ % & *).';
        }
        return '';
    }

    function apiUrl(action) {
        var endpoint = 'index.php?action=' + encodeURIComponent(action);
        // If opened directly as file://, force requests to Apache localhost.
        if (window.location.protocol === 'file:') {
            endpoint = 'http://localhost/projectttttttt/digiwork-hub/view/frontoffice/index.php?action=' + encodeURIComponent(action);
        }
        return endpoint;
    }

    function setAlert(el, message, kind) {
        if (!el) {
            return;
        }
        el.textContent = message || '';
        el.className = 'alert-message' + (message ? (kind === 'ok' ? ' alert-success' : ' alert-error') : '');
        el.style.display = message ? 'block' : 'none';
    }

    function clearAuthForm(modalId) {
        if (modalId === 'loginModal') {
            var loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.reset();
            }
            setAlert(document.getElementById('loginAlert'), '', 'err');
            resetLoginModalState();
        }

        if (modalId === 'signupModal') {
            var signupForm = document.getElementById('signupForm');
            if (signupForm) {
                signupForm.reset();
            }
            window.selectRole('candidat');
            setAlert(document.getElementById('signupAlert'), '', 'err');
            renderSignupPasswordStrength();
        }
    }

    window.openModal = function (id) {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.style.display = 'flex';
        }
    };

    window.closeModal = function (id) {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.style.display = 'none';
        }
        clearAuthForm(id);
    };

    window.switchToSignup = function () {
        window.closeModal('loginModal');
        window.openModal('signupModal');
    };

    window.switchToLogin = function () {
        window.closeModal('signupModal');
        window.openModal('loginModal');
    };

    let selectedRole = 'candidat';

    window.selectRole = function (role) {
        selectedRole = role;
        var roleInput = document.getElementById('signupRole');
        if (roleInput) {
            roleInput.value = role;
        }
        document.querySelectorAll('.role-option').forEach(function (el) {
            el.classList.toggle('selected', el.getAttribute('data-role') === role);
        });
        document.querySelectorAll('.dynamic-fields').forEach(function (el) {
            el.classList.remove('active');
        });
        var map = {
            candidat: 'candidatFields',
            entreprise: 'entrepriseFields',
            sponsor: 'sponsorFields',
            admin: 'adminFields',
        };
        var blockId = map[role];
        if (blockId) {
            var block = document.getElementById(blockId);
            if (block) {
                block.classList.add('active');
            }
        }
    };

    function validateLogin(email, password) {
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (password.length === 0) {
            return 'Mot de passe obligatoire.';
        }
        return '';
    }

    function validateSignupBase(email, tel, password, confirm) {
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (!telRe.test(tel.trim())) {
            return 'Telephone invalide (exactement 8 chiffres).';
        }
        if (password.length === 0) {
            return 'Mot de passe obligatoire.';
        }
        var pwdErr = validateSignupPasswordPolicy(password);
        if (pwdErr !== '') {
            return pwdErr;
        }
        if (password !== confirm) {
            return 'Les mots de passe ne correspondent pas.';
        }
        return '';
    }

    function validateSignupRole() {
        function nonEmpty(id) {
            var el = document.getElementById(id);
            return el && el.value.trim().length > 0;
        }

        if (selectedRole === 'candidat') {
            if (!nonEmpty('candidatNom') || !nonEmpty('candidatPrenom')) {
                return 'Nom et prenom obligatoires pour le candidat.';
            }
            var ddn = document.getElementById('candidatDdn');
            if (!ddn || !dateRe.test(ddn.value.trim())) {
                return 'Date de naissance au format AAAA-MM-JJ.';
            }
        } else if (selectedRole === 'entreprise') {
            if (!nonEmpty('entrepriseNom') || !nonEmpty('entrepriseAdresse')) {
                return 'Nom et adresse de l\'entreprise obligatoires.';
            }
        } else if (selectedRole === 'sponsor') {
            if (!nonEmpty('sponsorNom') || !nonEmpty('sponsorPrenom') || !nonEmpty('sponsorSociete')) {
                return 'Tous les champs sponsor sont obligatoires.';
            }
        } else if (selectedRole === 'admin') {
            if (!nonEmpty('adminNom') || !nonEmpty('adminPrenom') || !nonEmpty('adminCode')) {
                return 'Nom, prenom et code admin obligatoires.';
            }
            var adminDdn = document.getElementById('adminDdn');
            if (!adminDdn || !dateRe.test(adminDdn.value.trim())) {
                return 'Date de naissance admin au format AAAA-MM-JJ.';
            }
        }
        return '';
    }

    function ensureContactFeedback() {
        var form = document.getElementById('contactForm');
        if (!form) {
            return null;
        }
        var el = document.getElementById('contactFormFeedback');
        if (!el) {
            el = document.createElement('div');
            el.id = 'contactFormFeedback';
            el.setAttribute('role', 'status');
            el.style.cssText = 'display:none;margin-bottom:12px;padding:10px 12px;border-radius:8px;font-size:14px;';
            form.insertBefore(el, form.firstChild);
        }
        return el;
    }

    function showContactFeedback(text, isError) {
        var el = ensureContactFeedback();
        if (!el) {
            return;
        }
        el.textContent = text;
        el.style.display = text ? 'block' : 'none';
        el.style.background = isError ? '#fee' : '#efe';
        el.style.color = isError ? '#c00' : '#060';
        el.style.border = '1px solid ' + (isError ? '#fcc' : '#cfc');
    }

    function validateContact(name, email, message) {
        if (name.trim().length < 2) {
            return 'Nom trop court (minimum 2 caracteres).';
        }
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (message.trim().length < 10) {
            return 'Message trop court (minimum 10 caracteres).';
        }
        return '';
    }

    function parseApiResponse(response) {
        return response.text().then(function (raw) {
            try {
                return JSON.parse(raw);
            } catch (e) {
                var cleaned = (raw || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                return {
                    success: false,
                    message: cleaned || 'Reponse serveur invalide.',
                };
            }
        });
    }

    function startHeartbeat() {
        if (window.__FRONT_HEARTBEAT_TIMER__) {
            window.clearInterval(window.__FRONT_HEARTBEAT_TIMER__);
        }

        function ping() {
            fetch(apiUrl('heartbeat'), {
                method: 'GET',
                credentials: 'same-origin',
            }).catch(function () {});
        }

        ping();
        window.__FRONT_HEARTBEAT_TIMER__ = window.setInterval(ping, 60000);
    }

    function stopHeartbeat() {
        if (window.__FRONT_HEARTBEAT_TIMER__) {
            window.clearInterval(window.__FRONT_HEARTBEAT_TIMER__);
            window.__FRONT_HEARTBEAT_TIMER__ = null;
        }
    }

    let disconnectSent = false;

    function sendDisconnect() {
        if (disconnectSent) {
            return;
        }

        var authState = window.__FRONT_AUTH_STATE__ || {};
        if (!authState.loggedIn) {
            return;
        }

        disconnectSent = true;

        var logoutUrl = apiUrl('logout');
        if (navigator.sendBeacon) {
            try {
                navigator.sendBeacon(logoutUrl, new Blob([], { type: 'text/plain;charset=UTF-8' }));
                return;
            } catch (e) {
                // Fall through to fetch keepalive.
            }
        }

        fetch(logoutUrl, {
            method: 'GET',
            credentials: 'same-origin',
            keepalive: true,
        }).catch(function () {});
    }

    window.logoutFront = function () {
        disconnectSent = true;
        stopHeartbeat();

        var logoutUrl = apiUrl('logout');
        fetch(logoutUrl, {
            method: 'GET',
            credentials: 'same-origin',
        })
            .catch(function () {
                window.location.href = logoutUrl;
            })
            .finally(function () {
                window.location.href = 'index.php';
            });
    };

    function registerDisconnectHandlers() {
        window.addEventListener('pagehide', sendDisconnect);
        window.addEventListener('beforeunload', sendDisconnect);
    }

    document.addEventListener('DOMContentLoaded', function () {
        window.selectRole('candidat');

        var authState = window.__FRONT_AUTH_STATE__ || {};
        var authButtons = document.getElementById('authButtons');
        var frontUserInfo = document.getElementById('frontUserInfo');
        var frontLogoutButton = document.getElementById('frontLogoutButton');
        var frontUserEmail = document.getElementById('frontUserEmail');
        if (authState.loggedIn) {
            if (authButtons) {
                authButtons.classList.add('d-none');
            }
            if (frontUserInfo) {
                frontUserInfo.classList.remove('d-none');
            }
            if (frontLogoutButton) {
                frontLogoutButton.classList.remove('d-none');
            }
            if (frontUserEmail) {
                frontUserEmail.textContent = authState.email ? authState.email + (authState.role ? ' (' + authState.role + ')' : '') : '';
            }
        } else {
            if (authButtons) {
                authButtons.classList.remove('d-none');
            }
            if (frontUserInfo) {
                frontUserInfo.classList.add('d-none');
            }
            if (frontLogoutButton) {
                frontLogoutButton.classList.add('d-none');
            }
        }

        if (authState.loggedIn) {
            startHeartbeat();
            registerDisconnectHandlers();
        }

        var forgotLink = document.getElementById('forgotPasswordLink');
        if (forgotLink) {
            forgotLink.addEventListener('click', function (e) {
                e.preventDefault();
                showResetEmailForm();
            });
        }

        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var alertEl = document.getElementById('loginAlert');
                var email = document.getElementById('loginEmail').value;
                var password = document.getElementById('loginPassword').value;
                var err = validateLogin(email, password);
                if (err) {
                    setAlert(alertEl, err, 'err');
                    return;
                }
                setAlert(alertEl, '', 'err');
                fetch(apiUrl('login'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify({ email: email.trim(), password: password }),
                })
                    .then(parseApiResponse)
                    .then(function (data) {
                        if (data.success) {
                            setAlert(alertEl, data.message || 'Connexion reussie.', 'ok');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            window.closeModal('loginModal');
                            startHeartbeat();
                            registerDisconnectHandlers();
                        } else {
                            setAlert(alertEl, data.message || 'Echec de la connexion.', 'err');
                        }
                    })
                    .catch(function () {
                        setAlert(alertEl, 'Erreur reseau ou serveur.', 'err');
                    });
            });
        }

        var signupPasswordInput = document.getElementById('signupPassword');
        if (signupPasswordInput) {
            signupPasswordInput.addEventListener('input', renderSignupPasswordStrength);
        }

        var signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var alertEl = document.getElementById('signupAlert');
                var email = document.getElementById('signupEmail').value;
                var tel = document.getElementById('signupTel').value;
                var password = document.getElementById('signupPassword').value;
                var confirm = document.getElementById('signupConfirmPassword').value;
                var baseErr = validateSignupBase(email, tel, password, confirm);
                if (baseErr) {
                    setAlert(alertEl, baseErr, 'err');
                    return;
                }
                var roleErr = validateSignupRole();
                if (roleErr) {
                    setAlert(alertEl, roleErr, 'err');
                    return;
                }
                setAlert(alertEl, '', 'err');
                fetch(apiUrl('signup'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify({
                        email: email.trim(),
                        tel: tel.trim(),
                        password: password,
                        role: selectedRole,
                    }),
                })
                    .then(parseApiResponse)
                    .then(function (data) {
                        if (data.success && data.requires_otp) {
                            window.__OTP_USER_ID__ = data.user_id;
                            document.getElementById('signupForm').style.display = 'none';
                            showOtpForm(data.masked_tel);
                            startOtpCountdown(60);
                        } else if (data.success) {
                            setAlert(alertEl, data.message || 'Inscription reussie.', 'ok');
                            window.closeModal('signupModal');
                            window.openModal('loginModal');
                            setAlert(document.getElementById('loginAlert'), 'Vous pouvez vous connecter.', 'ok');
                        } else {
                            var failMsg =
                                (data.message && String(data.message).trim()) ||
                                (Array.isArray(data.errors) ? data.errors.join(' ') : '') ||
                                'Inscription refusee.';
                            setAlert(alertEl, failMsg, 'err');
                        }
                    })
                    .catch(function () {
                        setAlert(alertEl, 'Erreur reseau ou serveur.', 'err');
                    });
            });
        }

        var contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var name = document.getElementById('name');
                var email = document.getElementById('email');
                var message = document.getElementById('message');
                if (!name || !email || !message) {
                    return;
                }
                var msg = validateContact(name.value, email.value, message.value);
                if (msg) {
                    showContactFeedback(msg, true);
                    return;
                }
                showContactFeedback('Message valide (aucun envoi serveur configure pour ce formulaire).', false);
            });
        }
    });

    // ─── OTP helpers ────────────────────────────────────────────────────────────

    function showOtpForm(maskedTel) {
        var modal = document.getElementById('signupModal');
        if (!modal) return;
        var otpDiv = document.getElementById('otpFormContainer');
        if (!otpDiv) {
            otpDiv = document.createElement('div');
            otpDiv.id = 'otpFormContainer';
            var signupForm = document.getElementById('signupForm');
            if (signupForm && signupForm.parentNode) {
                signupForm.parentNode.insertBefore(otpDiv, signupForm.nextSibling);
            }
        }
        otpDiv.innerHTML =
            '<div style="text-align:center;padding:10px 0 20px;">' +
            '<p style="margin-bottom:15px;color:#555;font-size:14px;">Un code a ete envoye au <strong>' + maskedTel + '</strong></p>' +
            '<input type="text" id="otpCode" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" ' +
            'placeholder="_ _ _ _ _ _" autocomplete="one-time-code" ' +
            'style="width:160px;text-align:center;font-size:24px;letter-spacing:8px;padding:10px;border:2px solid #ddd;border-radius:8px;margin-bottom:12px;">' +
            '<div id="otpAlert" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:13px;"></div>' +
            '<button id="otpSubmitBtn" type="button" style="width:100%;padding:10px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:6px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:8px;">Valider le code</button>' +
            '<button id="otpResendBtn" type="button" disabled style="width:100%;padding:8px;background:#f5f5f5;color:#999;border:1px solid #ddd;border-radius:6px;font-size:13px;cursor:not-allowed;">' +
            'Renvoyer le code (<span id="otpCountdown">60</span>s)</button>' +
            '</div>';
        otpDiv.style.display = 'block';

        document.getElementById('otpSubmitBtn').addEventListener('click', submitOtp);
        document.getElementById('otpResendBtn').addEventListener('click', resendOtp);
    }

    function startOtpCountdown(seconds) {
        var remaining = seconds;
        var btn = document.getElementById('otpResendBtn');
        var span = document.getElementById('otpCountdown');
        if (!btn || !span) return;
        btn.disabled = true;
        btn.style.cursor = 'not-allowed';
        btn.style.color = '#999';
        var timer = setInterval(function () {
            remaining--;
            if (span) span.textContent = remaining;
            if (remaining <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.style.cursor = 'pointer';
                btn.style.color = '#333';
                btn.textContent = 'Renvoyer le code';
            }
        }, 1000);
    }

    function submitOtp() {
        var codeEl = document.getElementById('otpCode');
        var code = codeEl ? codeEl.value.trim() : '';
        var alertEl = document.getElementById('otpAlert');
        if (!/^[0-9]{6}$/.test(code)) {
            showOtpAlert(alertEl, 'Veuillez saisir un code a 6 chiffres.', 'err');
            return;
        }
        fetch(apiUrl('verify_otp'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ user_id: window.__OTP_USER_ID__, code: code }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (data.success) {
                    showOtpAlert(alertEl, 'Compte verifie ! Connexion en cours...', 'ok');
                    setTimeout(function () {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 1500);
                } else {
                    showOtpAlert(alertEl, data.message || 'Code invalide.', 'err');
                }
            })
            .catch(function () { showOtpAlert(alertEl, 'Erreur reseau.', 'err'); });
    }

    function resendOtp() {
        var alertEl = document.getElementById('otpAlert');
        fetch(apiUrl('resend_otp'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ user_id: window.__OTP_USER_ID__ }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (data.success) {
                    showOtpAlert(alertEl, 'Nouveau code envoye !', 'ok');
                    startOtpCountdown(60);
                } else {
                    var msg = data.remaining_seconds
                        ? 'Attendez encore ' + data.remaining_seconds + ' secondes.'
                        : (data.message || 'Erreur lors du renvoi.');
                    showOtpAlert(alertEl, msg, 'err');
                }
            })
            .catch(function () { showOtpAlert(alertEl, 'Erreur reseau.', 'err'); });
    }

    function showOtpAlert(el, msg, kind) {
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
        el.style.background = kind === 'ok' ? '#efe' : '#fee';
        el.style.color = kind === 'ok' ? '#060' : '#c00';
        el.style.border = '1px solid ' + (kind === 'ok' ? '#cfc' : '#fcc');
    }

    // ─── Reset_Flow helpers ──────────────────────────────────────────────────────

    /**
     * Réinitialise le loginModal à son état initial (loginForm visible, containers reset supprimés).
     * Appelé par clearAuthForm('loginModal') et après un reset réussi.
     */
    function resetLoginModalState() {
        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.style.display = '';
        }
        var ids = ['resetEmailContainer', 'resetOtpContainer', 'resetPasswordContainer'];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });
        window.__RESET_USER_ID__ = null;
    }

    /**
     * Affiche le formulaire de saisie d'email dans le loginModal.
     * Masque #loginForm et injecte #resetEmailContainer.
     */
    function showResetEmailForm() {
        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.style.display = 'none';
        }

        var container = document.getElementById('resetEmailContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'resetEmailContainer';
            var modalBody = loginForm ? loginForm.parentNode : null;
            if (modalBody) {
                modalBody.insertBefore(container, loginForm.nextSibling);
            }
        }

        container.innerHTML =
            '<div style="padding:10px 0 20px;">' +
            '<p style="margin-bottom:15px;color:#555;font-size:14px;">Saisissez votre adresse email pour recevoir un code par SMS.</p>' +
            '<div id="resetEmailAlert" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:13px;"></div>' +
            '<div class="form-group">' +
            '<label>Email</label>' +
            '<input type="text" id="resetEmailInput" placeholder="exemple@domaine.com" autocomplete="email">' +
            '</div>' +
            '<button id="resetEmailSubmitBtn" type="button" class="btn-submit" style="margin-bottom:10px;">Envoyer le code SMS</button>' +
            '<div style="text-align:center;">' +
            '<a href="#" id="resetBackToLoginLink" style="font-size:13px;color:#667eea;text-decoration:none;">Retour à la connexion</a>' +
            '</div>' +
            '</div>';
        container.style.display = 'block';

        document.getElementById('resetEmailSubmitBtn').addEventListener('click', submitForgotPassword);
        document.getElementById('resetBackToLoginLink').addEventListener('click', function (e) {
            e.preventDefault();
            resetLoginModalState();
        });
    }

    /**
     * Affiche le formulaire OTP reset avec le numéro masqué et le countdown.
     * Masque #resetEmailContainer et affiche #resetOtpContainer.
     */
    function showResetOtpForm(maskedTel, userId) {
        window.__RESET_USER_ID__ = userId;

        var emailContainer = document.getElementById('resetEmailContainer');
        if (emailContainer) {
            emailContainer.style.display = 'none';
        }

        var container = document.getElementById('resetOtpContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'resetOtpContainer';
            var loginForm = document.getElementById('loginForm');
            var parent = loginForm ? loginForm.parentNode : (emailContainer ? emailContainer.parentNode : null);
            if (parent) {
                var ref = emailContainer || loginForm;
                parent.insertBefore(container, ref ? ref.nextSibling : null);
            }
        }

        container.innerHTML =
            '<div style="text-align:center;padding:10px 0 20px;">' +
            '<p style="margin-bottom:15px;color:#555;font-size:14px;">Un code a ete envoye au <strong>' + maskedTel + '</strong></p>' +
            '<div id="resetOtpAlert" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:13px;"></div>' +
            '<input type="text" id="resetOtpCode" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" ' +
            'placeholder="_ _ _ _ _ _" autocomplete="one-time-code" ' +
            'style="width:160px;text-align:center;font-size:24px;letter-spacing:8px;padding:10px;border:2px solid #ddd;border-radius:8px;margin-bottom:12px;">' +
            '<button id="resetOtpSubmitBtn" type="button" style="width:100%;padding:10px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:6px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:8px;">Valider le code</button>' +
            '<button id="resetOtpResendBtn" type="button" disabled style="width:100%;padding:8px;background:#f5f5f5;color:#999;border:1px solid #ddd;border-radius:6px;font-size:13px;cursor:not-allowed;">' +
            'Renvoyer le code (<span id="resetOtpCountdown">60</span>s)</button>' +
            '</div>';
        container.style.display = 'block';

        document.getElementById('resetOtpSubmitBtn').addEventListener('click', submitVerifyResetOtp);
        document.getElementById('resetOtpResendBtn').addEventListener('click', submitResendResetOtp);

        startResetOtpCountdown(60);
    }

    /**
     * Affiche le formulaire de saisie du nouveau mot de passe.
     * Masque #resetOtpContainer et affiche #resetPasswordContainer.
     */
    function showResetPasswordForm(userId) {
        window.__RESET_USER_ID__ = userId;

        var otpContainer = document.getElementById('resetOtpContainer');
        if (otpContainer) {
            otpContainer.style.display = 'none';
        }

        var container = document.getElementById('resetPasswordContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'resetPasswordContainer';
            var loginForm = document.getElementById('loginForm');
            var parent = loginForm ? loginForm.parentNode : (otpContainer ? otpContainer.parentNode : null);
            if (parent) {
                var ref = otpContainer || loginForm;
                parent.insertBefore(container, ref ? ref.nextSibling : null);
            }
        }

        container.innerHTML =
            '<div style="padding:10px 0 20px;">' +
            '<p style="margin-bottom:15px;color:#555;font-size:14px;">Choisissez un nouveau mot de passe.</p>' +
            '<div id="resetPasswordAlert" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:13px;"></div>' +
            '<div class="form-group">' +
            '<label>Nouveau mot de passe</label>' +
            '<div id="resetPasswordStrength" style="display:none;"></div>' +
            '<input type="password" id="resetNewPassword" placeholder="10+ caracteres, maj/min, chiffre, symbole" autocomplete="new-password">' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Confirmer le mot de passe</label>' +
            '<input type="password" id="resetConfirmPassword" placeholder="Confirmer le mot de passe" autocomplete="new-password">' +
            '</div>' +
            '<button id="resetPasswordSubmitBtn" type="button" class="btn-submit">Enregistrer le mot de passe</button>' +
            '</div>';
        container.style.display = 'block';

        // Indicateur de force du mot de passe (réutilise analyzePasswordSignup)
        var newPwdInput = document.getElementById('resetNewPassword');
        if (newPwdInput) {
            newPwdInput.addEventListener('input', function () {
                renderResetPasswordStrength(newPwdInput.value);
            });
        }

        document.getElementById('resetPasswordSubmitBtn').addEventListener('click', submitResetPassword);
    }

    /**
     * Affiche l'indicateur de force du mot de passe dans le formulaire de reset.
     * Réutilise analyzePasswordSignup.
     */
    function renderResetPasswordStrength(value) {
        var box = document.getElementById('resetPasswordStrength');
        if (!box) return;

        if (!value) {
            box.innerHTML = '';
            box.style.display = 'none';
            return;
        }

        var a = analyzePasswordSignup(value);
        box.style.display = 'block';

        var label, color, bg, border;
        if (a.hasSpace) {
            label = 'Espaces non autorisés dans le mot de passe';
            color = '#c0392b'; bg = '#fdecea'; border = '1px solid #f5c6cb';
        } else if (a.met <= 2) {
            label = 'Faible';
            color = '#c0392b'; bg = '#fdecea'; border = '1px solid #f5c6cb';
        } else if (a.met <= 4) {
            label = 'Moyen';
            color = '#e67e22'; bg = '#fff3e0'; border = '1px solid #ffe0b2';
        } else {
            label = 'Fort';
            color = '#27ae60'; bg = '#eafaf1'; border = '1px solid #c3e6cb';
        }

        box.style.cssText = 'display:block;padding:6px 10px;border-radius:6px;font-size:13px;margin-bottom:6px;' +
            'color:' + color + ';background:' + bg + ';border:' + border + ';';

        var criteriaLabels = {
            longueur: 'au moins 10 caractères',
            majuscule: 'une majuscule',
            minuscule: 'une minuscule',
            chiffre: 'un chiffre',
            special: 'un symbole (ex: @ # $ % &)',
        };
        var missing = Object.keys(a.checks).filter(function (k) { return !a.checks[k]; });
        var missingText = missing.length > 0
            ? '<div style="margin-top:3px;font-size:12px;">Manque : ' + missing.map(function (k) { return criteriaLabels[k]; }).join(', ') + '</div>'
            : '';
        box.innerHTML = '<strong>' + label + '</strong>' + missingText;
    }

    /**
     * Countdown anti-spam pour le renvoi du code OTP reset.
     */
    function startResetOtpCountdown(seconds) {
        var remaining = seconds;
        var btn = document.getElementById('resetOtpResendBtn');
        var span = document.getElementById('resetOtpCountdown');
        if (!btn || !span) return;
        btn.disabled = true;
        btn.style.cursor = 'not-allowed';
        btn.style.color = '#999';
        var timer = setInterval(function () {
            remaining--;
            if (span) span.textContent = remaining;
            if (remaining <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.style.cursor = 'pointer';
                btn.style.color = '#333';
                btn.textContent = 'Renvoyer le code';
            }
        }, 1000);
    }

    /**
     * Affiche une alerte dans un container du Reset_Flow.
     */
    function showResetAlert(elId, msg, kind) {
        var el = document.getElementById(elId);
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
        el.style.background = kind === 'ok' ? '#efe' : '#fee';
        el.style.color = kind === 'ok' ? '#060' : '#c00';
        el.style.border = '1px solid ' + (kind === 'ok' ? '#cfc' : '#fcc');
    }

    // ─── Appels AJAX Reset_Flow ──────────────────────────────────────────────────

    /**
     * Soumet l'email pour déclencher l'envoi du SMS OTP reset.
     */
    function submitForgotPassword() {
        var emailEl = document.getElementById('resetEmailInput');
        var email = emailEl ? emailEl.value.trim() : '';

        if (!emailRe.test(email)) {
            showResetAlert('resetEmailAlert', 'Email invalide.', 'err');
            return;
        }

        showResetAlert('resetEmailAlert', '', 'err');
        var btn = document.getElementById('resetEmailSubmitBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Envoi en cours...'; }

        fetch(apiUrl('forgot_password'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ email: email }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (btn) { btn.disabled = false; btn.textContent = 'Envoyer le code SMS'; }
                if (data.success && data.user_id) {
                    showResetOtpForm(data.masked_tel, data.user_id);
                } else if (data.success) {
                    // Réponse générique anti-énumération : pas de user_id → afficher message
                    showResetAlert('resetEmailAlert', data.message || 'Si un compte existe, un SMS a ete envoye.', 'ok');
                } else {
                    showResetAlert('resetEmailAlert', data.message || 'Erreur lors de l\'envoi.', 'err');
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = 'Envoyer le code SMS'; }
                showResetAlert('resetEmailAlert', 'Erreur reseau ou serveur.', 'err');
            });
    }

    /**
     * Soumet le code OTP reset pour vérification.
     */
    function submitVerifyResetOtp() {
        var codeEl = document.getElementById('resetOtpCode');
        var code = codeEl ? codeEl.value.trim() : '';

        if (!/^[0-9]{6}$/.test(code)) {
            showResetAlert('resetOtpAlert', 'Veuillez saisir un code a 6 chiffres.', 'err');
            return;
        }

        showResetAlert('resetOtpAlert', '', 'err');

        fetch(apiUrl('verify_reset_otp'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ user_id: window.__RESET_USER_ID__, code: code }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (data.success) {
                    showResetPasswordForm(window.__RESET_USER_ID__);
                } else {
                    showResetAlert('resetOtpAlert', data.message || 'Code invalide.', 'err');
                }
            })
            .catch(function () {
                showResetAlert('resetOtpAlert', 'Erreur reseau ou serveur.', 'err');
            });
    }

    /**
     * Demande le renvoi d'un nouveau code OTP reset.
     */
    function submitResendResetOtp() {
        fetch(apiUrl('resend_reset_otp'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ user_id: window.__RESET_USER_ID__ }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (data.success) {
                    showResetAlert('resetOtpAlert', 'Nouveau code envoye !', 'ok');
                    startResetOtpCountdown(60);
                } else {
                    var msg = data.remaining_seconds
                        ? 'Attendez encore ' + data.remaining_seconds + ' secondes.'
                        : (data.message || 'Erreur lors du renvoi.');
                    showResetAlert('resetOtpAlert', msg, 'err');
                }
            })
            .catch(function () {
                showResetAlert('resetOtpAlert', 'Erreur reseau ou serveur.', 'err');
            });
    }

    /**
     * Soumet le nouveau mot de passe pour finaliser la réinitialisation.
     */
    function submitResetPassword() {
        var newPwdEl = document.getElementById('resetNewPassword');
        var confirmEl = document.getElementById('resetConfirmPassword');
        var newPassword = newPwdEl ? newPwdEl.value : '';
        var confirmPassword = confirmEl ? confirmEl.value : '';

        // Validation côté client
        var pwdErr = validateSignupPasswordPolicy(newPassword);
        if (pwdErr) {
            showResetAlert('resetPasswordAlert', pwdErr, 'err');
            return;
        }
        if (newPassword !== confirmPassword) {
            showResetAlert('resetPasswordAlert', 'Les mots de passe ne correspondent pas.', 'err');
            return;
        }

        showResetAlert('resetPasswordAlert', '', 'err');
        var btn = document.getElementById('resetPasswordSubmitBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Enregistrement...'; }

        fetch(apiUrl('reset_password'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({ user_id: window.__RESET_USER_ID__, new_password: newPassword }),
        })
            .then(parseApiResponse)
            .then(function (data) {
                if (btn) { btn.disabled = false; btn.textContent = 'Enregistrer le mot de passe'; }
                if (data.success) {
                    // Réinitialiser le modal et afficher le message de succès dans loginAlert
                    resetLoginModalState();
                    var loginForm = document.getElementById('loginForm');
                    if (loginForm) {
                        loginForm.style.display = '';
                    }
                    setAlert(
                        document.getElementById('loginAlert'),
                        'Mot de passe reinitialise avec succes. Vous pouvez vous connecter.',
                        'ok'
                    );
                } else {
                    showResetAlert('resetPasswordAlert', data.message || 'Erreur lors de la reinitialisation.', 'err');
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = 'Enregistrer le mot de passe'; }
                showResetAlert('resetPasswordAlert', 'Erreur reseau ou serveur.', 'err');
            });
    }

})();
