<template>
  <div class="min-h-screen bg-slate-50 text-slate-800">
    <!-- Toast Notification -->
    <div v-if="toast.show" class="fixed top-5 right-5 z-50 transition-all">
      <div
        :class="toast.success ? 'bg-emerald-600' : 'bg-rose-600'"
        class="flex items-center gap-2.5 px-5 py-3.5 rounded-xl shadow-lg text-white text-sm font-medium"
      >
        <span>{{ toast.success ? '✓' : '!' }}</span>
        <span>{{ toast.message }}</span>
      </div>
    </div>

    <!-- ==================== AUTH SCREEN ==================== -->
    <div
      v-if="!currentUser"
      class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-50 via-white to-blue-50"
    >
      <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="bg-indigo-600 p-6 text-white text-center">
          <h1 class="text-xl font-bold">ระบบยื่นใบลาออนไลน์</h1>
          <p class="text-indigo-100 text-xs mt-1">Vue.js + PHP + MySQL</p>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-slate-200">
          <button
            @click="authTab = 'login'"
            :class="authTab === 'login' ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-slate-500'"
            class="flex-1 py-3 text-sm"
          >
            เข้าสู่ระบบ
          </button>
          <button
            @click="authTab = 'register'"
            :class="authTab === 'register' ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold' : 'text-slate-500'"
            class="flex-1 py-3 text-sm"
          >
            สมัครสมาชิก
          </button>
        </div>

        <!-- Login Form -->
        <form v-if="authTab === 'login'" @submit.prevent="handleLogin" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">ชื่อผู้ใช้งาน (Username)</label>
            <input
              v-model="loginForm.username"
              type="text"
              required
              placeholder="เช่น SNOW หรือ SALMON"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">รหัสผ่าน (Password)</label>
            <input
              v-model="loginForm.password"
              type="password"
              required
              placeholder="กรอกรหัสผ่าน"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
            />
          </div>
          <button
            type="submit"
            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition"
          >
            เข้าสู่ระบบ
          </button>

          <!-- Quick Demo Login -->
          <div class="pt-4 border-t border-slate-100">
            <p class="text-xs text-slate-400 text-center mb-2.5">เข้าสู่ระบบด่วนสำหรับทดสอบ (รหัสผ่าน 1234)</p>
            <div class="grid grid-cols-3 gap-2">
              <button
                type="button"
                @click="quickLogin('SNOW', '1234')"
                class="px-2 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-medium border border-purple-200"
              >
                SNOW (HR)
              </button>
              <button
                type="button"
                @click="quickLogin('SALMON', '1234')"
                class="px-2 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium border border-blue-200"
              >
                SALMON (IT)
              </button>
              <button
                type="button"
                @click="quickLogin('suda', '1234')"
                class="px-2 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-medium border border-emerald-200"
              >
                สุดา (MKT)
              </button>
            </div>
          </div>
        </form>

        <!-- Register Form -->
        <form v-else @submit.prevent="handleRegister" class="p-6 space-y-3.5">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">ชื่อ-นามสกุล</label>
            <input
              v-model="registerForm.full_name"
              type="text"
              required
              placeholder="เช่น กิตติพงษ์ รักดี"
              class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm outline-none"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">แผนก / ฝ่าย</label>
            <input
              v-model="registerForm.department"
              type="text"
              required
              placeholder="เช่น บัญชี, ไอที, ขาย"
              class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm outline-none"
            />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Username</label>
              <input
                v-model="registerForm.username"
                type="text"
                required
                class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">รหัสผ่าน</label>
              <input
                v-model="registerForm.password"
                type="password"
                required
                class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm outline-none"
              />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">บทบาทผู้ใช้งาน</label>
            <select v-model="registerForm.role" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm outline-none">
              <option value="user">พนักงานทั่วไป (User)</option>
              <option value="admin">ผู้ดูแลระบบ (Admin)</option>
            </select>
          </div>
          <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm">
            สร้างบัญชีและเข้าใช้งาน
          </button>
        </form>
      </div>
    </div>

    <!-- ==================== MAIN APP SCREEN ==================== -->
    <div v-else>
      <!-- Navbar -->
      <nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold">
              LS
            </div>
            <div>
              <span class="font-bold text-slate-800 text-base">LeaveSystem</span>
              <span class="hidden sm:inline-block ml-2 text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                Vue + MySQL
              </span>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-right">
              <div class="text-sm font-semibold text-slate-800">{{ currentUser.full_name }}</div>
              <div class="flex items-center justify-end gap-1.5">
                <span class="text-xs text-slate-500">{{ currentUser.department }}</span>
                <span
                  :class="currentUser.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                  class="text-[11px] font-semibold px-2 py-0.5 rounded-full uppercase"
                >
                  {{ currentUser.role }}
                </span>
              </div>
            </div>
            <button
              @click="handleLogout"
              class="ml-2 px-3 py-2 rounded-lg text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 transition"
            >
              ออกจากระบบ
            </button>
          </div>
        </div>
      </nav>

      <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">
        <!-- ==================== USER DASHBOARD ==================== -->
        <div v-if="currentUser.role !== 'admin'" class="space-y-6">
          <!-- Quota Cards -->
          <div>
            <h2 class="text-base font-bold text-slate-800 mb-3">โควตาวันลาคงเหลือของคุณ</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div
                v-for="q in userQuotas"
                :key="q.id"
                class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm"
              >
                <div class="flex justify-between items-start">
                  <span class="text-sm font-bold text-slate-800">{{ q.name }}</span>
                  <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-medium">
                    ใช้ไป {{ q.used }} วัน
                  </span>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                  <span class="text-2xl font-bold text-indigo-600">{{ q.remain }}</span>
                  <span class="text-xs text-slate-400">/ {{ q.max_days }} วันคงเหลือ</span>
                </div>
                <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2.5 overflow-hidden">
                  <div class="bg-indigo-600 h-full rounded-full" :style="{ width: q.pct + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Create Leave Form -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 h-fit">
              <h3 class="text-base font-bold text-slate-800 mb-4">แบบฟอร์มยื่นใบลา</h3>
              <form @submit.prevent="handleCreateLeave" class="space-y-3.5">
                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">ประเภทใบลา</label>
                  <select
                    v-model="createForm.leave_type_id"
                    required
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                  >
                    <option v-for="t in activeLeaveTypes" :key="t.id" :value="t.id">
                      {{ t.name }} (สูงสุด {{ t.max_days }} วัน)
                    </option>
                  </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">วันที่เริ่มลา</label>
                    <input
                      v-model="createForm.start_date"
                      @change="recalcCreateDays"
                      type="date"
                      required
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">ถึงวันที่</label>
                    <input
                      v-model="createForm.end_date"
                      @change="recalcCreateDays"
                      type="date"
                      required
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                    />
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">ช่วงเวลาการลา</label>
                    <select
                      v-model="createForm.leave_period"
                      @change="recalcCreateDays"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                    >
                      <option value="full">เต็มวัน</option>
                      <option value="morning">ครึ่งวันเช้า (0.5)</option>
                      <option value="afternoon">ครึ่งวันบ่าย (0.5)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">จำนวนวันลา (วัน)</label>
                    <input
                      v-model="createForm.total_days"
                      type="number"
                      step="0.5"
                      readonly
                      class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm font-semibold text-indigo-600"
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">เหตุผลการลา</label>
                  <textarea
                    v-model="createForm.reason"
                    rows="3"
                    required
                    placeholder="ระบุรายละเอียดเหตุผลการลา..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                  ></textarea>
                </div>
                <button
                  type="submit"
                  class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition"
                >
                  ส่งใบลา
                </button>
              </form>
            </div>

            <!-- My Leave History -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
              <h3 class="text-base font-bold text-slate-800 mb-4">ประวัติการยื่นใบลาของฉัน</h3>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 bg-slate-50">
                      <th class="py-3 px-3">ประเภทใบลา</th>
                      <th class="py-3 px-3">วันที่ลา</th>
                      <th class="py-3 px-3">จำนวน</th>
                      <th class="py-3 px-3">เหตุผล / หมายเหตุ Admin</th>
                      <th class="py-3 px-3">สถานะ</th>
                      <th class="py-3 px-3 text-right">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-sm">
                    <tr v-if="appData.my_leaves.length === 0">
                      <td colspan="6" class="py-8 text-center text-slate-400">ยังไม่มีประวัติการยื่นใบลา</td>
                    </tr>
                    <tr v-for="l in appData.my_leaves" :key="l.id" class="hover:bg-slate-50">
                      <td class="py-3 px-3 font-medium text-slate-800">{{ l.leave_type_name }}</td>
                      <td class="py-3 px-3 text-xs text-slate-600">
                        {{ l.start_date }} {{ l.start_date !== l.end_date ? 'ถึง ' + l.end_date : '' }}
                        <div class="text-slate-400">{{ periodLabel(l.leave_period) }}</div>
                      </td>
                      <td class="py-3 px-3 font-semibold text-slate-700">{{ l.total_days }} วัน</td>
                      <td class="py-3 px-3 text-xs text-slate-600 max-w-xs">
                        <div>{{ l.reason }}</div>
                        <div v-if="l.admin_remark" class="mt-1 text-indigo-600 font-medium">
                          หมายเหตุ HR: {{ l.admin_remark }}
                        </div>
                      </td>
                      <td class="py-3 px-3">
                        <span :class="statusBadgeClass(l.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                          {{ statusLabel(l.status) }}
                        </span>
                      </td>
                      <td class="py-3 px-3 text-right space-x-1">
                        <template v-if="l.status === 'pending'">
                          <button
                            @click="openEditModal(l)"
                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-xs font-medium"
                          >
                            แก้ไข
                          </button>
                          <button
                            @click="deleteLeave(l.id)"
                            class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-xs font-medium"
                          >
                            ยกเลิก
                          </button>
                        </template>
                        <span v-else class="text-xs text-slate-400">-</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== ADMIN DASHBOARD ==================== -->
        <div v-else class="space-y-6">
          <!-- Stats -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
              <p class="text-xs text-slate-500">ใบลาทั้งหมดในระบบ</p>
              <p class="text-2xl font-bold text-slate-800 mt-1">{{ appData.all_leaves.length }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm">
              <p class="text-xs text-amber-600 font-medium">รออนุมัติ (Pending)</p>
              <p class="text-2xl font-bold text-amber-600 mt-1">{{ adminStats.pending }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm">
              <p class="text-xs text-emerald-600 font-medium">อนุมัติแล้ว (Approved)</p>
              <p class="text-2xl font-bold text-emerald-600 mt-1">{{ adminStats.approved }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm">
              <p class="text-xs text-rose-600 font-medium">ไม่อนุมัติ (Rejected)</p>
              <p class="text-2xl font-bold text-rose-600 mt-1">{{ adminStats.rejected }}</p>
            </div>
          </div>

          <!-- Admin Tabs -->
          <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
            <button
              @click="adminTab = 'leaves'"
              :class="adminTab === 'leaves' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
              class="px-4 py-2 rounded-lg text-sm font-medium"
            >
              รายการใบลา & อนุมัติ
            </button>
            <button
              @click="adminTab = 'types'"
              :class="adminTab === 'types' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
              class="px-4 py-2 rounded-lg text-sm font-medium"
            >
              จัดการประเภทใบลา
            </button>
            <button
              @click="adminTab = 'users'"
              :class="adminTab === 'users' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200'"
              class="px-4 py-2 rounded-lg text-sm font-medium"
            >
              จัดการผู้ใช้งาน
            </button>
          </div>

          <!-- TAB 1: ALL LEAVES -->
          <div v-if="adminTab === 'leaves'" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex flex-col sm:flex-row gap-3 justify-between">
              <input
                v-model="adminFilter.search"
                type="text"
                placeholder="ค้นหาชื่อพนักงาน, แผนก, เหตุผล..."
                class="px-3.5 py-2 rounded-lg border border-slate-300 text-sm w-full sm:w-72 outline-none"
              />
              <div class="flex gap-2">
                <select v-model="adminFilter.status" class="px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none">
                  <option value="all">ทุกสถานะ</option>
                  <option value="pending">รออนุมัติ</option>
                  <option value="approved">อนุมัติแล้ว</option>
                  <option value="rejected">ไม่อนุมัติ</option>
                </select>
                <select v-model="adminFilter.type" class="px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none">
                  <option value="all">ทุกประเภทใบลา</option>
                  <option v-for="t in appData.leave_types" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
                </select>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-slate-200 text-xs text-slate-500 bg-slate-50">
                    <th class="py-3 px-3">พนักงาน / แผนก</th>
                    <th class="py-3 px-3">ประเภทใบลา</th>
                    <th class="py-3 px-3">ช่วงวันที่ลา</th>
                    <th class="py-3 px-3">เหตุผล & หมายเหตุ</th>
                    <th class="py-3 px-3">สถานะ</th>
                    <th class="py-3 px-3 text-right">อนุมัติ / แก้ไข</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                  <tr v-if="filteredAdminLeaves.length === 0">
                    <td colspan="6" class="py-8 text-center text-slate-400">ไม่พบรายการใบลาที่ตรงกับเงื่อนไข</td>
                  </tr>
                  <tr v-for="l in filteredAdminLeaves" :key="l.id" class="hover:bg-slate-50">
                    <td class="py-3 px-3">
                      <div class="font-semibold text-slate-800">{{ l.user_name }}</div>
                      <div class="text-xs text-slate-400">{{ l.department }}</div>
                    </td>
                    <td class="py-3 px-3 font-medium text-slate-700">{{ l.leave_type_name }}</td>
                    <td class="py-3 px-3 text-xs text-slate-600">
                      <div>{{ l.start_date }} {{ l.start_date !== l.end_date ? 'ถึง ' + l.end_date : '' }}</div>
                      <div class="font-semibold text-indigo-600">{{ l.total_days }} วัน ({{ periodLabel(l.leave_period) }})</div>
                    </td>
                    <td class="py-3 px-3 text-xs text-slate-600 max-w-xs">
                      <div>{{ l.reason }}</div>
                      <div v-if="l.admin_remark" class="mt-1 text-purple-600 font-medium">หมายเหตุ: {{ l.admin_remark }}</div>
                    </td>
                    <td class="py-3 px-3">
                      <span :class="statusBadgeClass(l.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">
                        {{ statusLabel(l.status) }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-right space-x-1 whitespace-nowrap">
                      <button
                        @click="openStatusModal(l.id, 'approved')"
                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium"
                      >
                        อนุมัติ
                      </button>
                      <button
                        @click="openStatusModal(l.id, 'rejected')"
                        class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-medium"
                      >
                        ไม่อนุมัติ
                      </button>
                      <button
                        @click="openEditModal(l)"
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium"
                      >
                        แก้ไข
                      </button>
                      <button @click="deleteLeave(l.id)" class="px-2 py-1 text-rose-500 hover:bg-rose-50 rounded text-xs">
                        ลบ
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 2: LEAVE TYPES -->
          <div v-if="adminTab === 'types'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 h-fit">
              <h3 class="text-base font-bold text-slate-800 mb-4">
                {{ typeForm.id ? 'แก้ไขประเภทใบลา' : 'เพิ่มประเภทใบลาใหม่' }}
              </h3>
              <form @submit.prevent="handleSaveLeaveType" class="space-y-3.5">
                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">ชื่อประเภทใบลา</label>
                  <input
                    v-model="typeForm.name"
                    type="text"
                    required
                    placeholder="เช่น ลาพักร้อน, ลาคลอด"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">โควตาสูงสุดต่อปี (วัน)</label>
                  <input
                    v-model="typeForm.max_days"
                    type="number"
                    min="1"
                    required
                    placeholder="เช่น 10"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-slate-600 mb-1">คำอธิบายเงื่อนไข</label>
                  <input
                    v-model="typeForm.description"
                    type="text"
                    placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
                  />
                </div>
                <div class="flex gap-2">
                  <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm">
                    บันทึกข้อมูล
                  </button>
                  <button
                    v-if="typeForm.id"
                    type="button"
                    @click="resetTypeForm"
                    class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm"
                  >
                    ยกเลิก
                  </button>
                </div>
              </form>
            </div>

            <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
              <h3 class="text-base font-bold text-slate-800 mb-4">รายการประเภทใบลาทั้งหมด</h3>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 bg-slate-50">
                      <th class="py-3 px-3">ชื่อประเภท</th>
                      <th class="py-3 px-3">โควตา/ปี</th>
                      <th class="py-3 px-3">คำอธิบาย</th>
                      <th class="py-3 px-3">สถานะ</th>
                      <th class="py-3 px-3 text-right">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-sm">
                    <tr v-for="t in appData.leave_types" :key="t.id" class="hover:bg-slate-50">
                      <td class="py-3 px-3 font-semibold text-slate-800">{{ t.name }}</td>
                      <td class="py-3 px-3 font-medium text-indigo-600">{{ t.max_days }} วัน</td>
                      <td class="py-3 px-3 text-xs text-slate-500">{{ t.description || '-' }}</td>
                      <td class="py-3 px-3">
                        <button
                          @click="toggleLeaveType(t.id)"
                          :class="Number(t.is_active) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                          class="px-2.5 py-1 rounded-full text-xs font-medium"
                        >
                          {{ Number(t.is_active) === 1 ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </button>
                      </td>
                      <td class="py-3 px-3 text-right space-x-1">
                        <button
                          @click="editLeaveType(t)"
                          class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-xs font-medium"
                        >
                          แก้ไข
                        </button>
                        <button
                          @click="deleteLeaveType(t.id)"
                          class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-xs font-medium"
                        >
                          ลบ
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 3: USERS -->
          <div v-if="adminTab === 'users'" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-base font-bold text-slate-800 mb-4">รายชื่อผู้ใช้งานและสิทธิ์การเข้าถึง</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="border-b border-slate-200 text-xs text-slate-500 bg-slate-50">
                    <th class="py-3 px-3">ชื่อ-นามสกุล</th>
                    <th class="py-3 px-3">Username</th>
                    <th class="py-3 px-3">แผนก</th>
                    <th class="py-3 px-3">จำนวนใบลา</th>
                    <th class="py-3 px-3">สิทธิ์ปัจจุบัน</th>
                    <th class="py-3 px-3 text-right">เปลี่ยนสิทธิ์</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                  <tr v-for="u in appData.users" :key="u.id" class="hover:bg-slate-50">
                    <td class="py-3 px-3 font-semibold text-slate-800">{{ u.full_name }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ u.username }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ u.department }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ u.total_requests }} รายการ</td>
                    <td class="py-3 px-3">
                      <span
                        :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase"
                      >
                        {{ u.role }}
                      </span>
                    </td>
                    <td class="py-3 px-3 text-right">
                      <button
                        v-if="Number(u.id) !== Number(currentUser.id)"
                        @click="toggleUserRole(u.id)"
                        class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium"
                      >
                        สลับเป็น {{ u.role === 'admin' ? 'User' : 'Admin' }}
                      </button>
                      <span v-else class="text-xs text-slate-400">บัญชีปัจจุบัน</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- ==================== MODAL: EDIT LEAVE ==================== -->
    <div v-if="editModal.show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800">แก้ไขรายละเอียดใบลา</h3>
          <button @click="editModal.show = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form @submit.prevent="handleUpdateLeave" class="space-y-3.5">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">ประเภทใบลา</label>
            <select v-model="editModal.form.leave_type_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
              <option v-for="t in appData.leave_types" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">วันที่เริ่มลา</label>
              <input v-model="editModal.form.start_date" @change="recalcEditDays" type="date" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">ถึงวันที่</label>
              <input v-model="editModal.form.end_date" @change="recalcEditDays" type="date" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">ช่วงเวลา</label>
              <select v-model="editModal.form.leave_period" @change="recalcEditDays" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                <option value="full">เต็มวัน</option>
                <option value="morning">ครึ่งวันเช้า</option>
                <option value="afternoon">ครึ่งวันบ่าย</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">จำนวนวันลา</label>
              <input v-model="editModal.form.total_days" type="number" step="0.5" readonly class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm font-semibold" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">เหตุผลการลา</label>
            <textarea v-model="editModal.form.reason" rows="2" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"></textarea>
          </div>

          <div v-if="currentUser && currentUser.role === 'admin'" class="space-y-3 pt-2 border-t border-slate-100">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">สถานะการอนุมัติ (Admin)</label>
              <select v-model="editModal.form.status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                <option value="pending">รออนุมัติ (Pending)</option>
                <option value="approved">อนุมัติ (Approved)</option>
                <option value="rejected">ไม่อนุมัติ (Rejected)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">หมายเหตุจากผู้ดูแลระบบ</label>
              <input v-model="editModal.form.admin_remark" type="text" placeholder="ระบุเหตุผลประกอบการพิจารณา" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm" />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="editModal.show = false" class="px-4 py-2 rounded-lg text-sm bg-slate-100 text-slate-600">ยกเลิก</button>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm text-white bg-indigo-600 hover:bg-indigo-700 font-medium">บันทึกการแก้ไข</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL: APPROVE / REJECT ==================== -->
    <div v-if="statusModal.show" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl space-y-4">
        <h3 class="font-bold text-slate-800">
          {{ statusModal.status === 'approved' ? 'ยืนยันการอนุมัติใบลา' : 'ยืนยันการไม่อนุมัติใบลา' }}
        </h3>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">หมายเหตุถึงพนักงาน (ไม่บังคับ)</label>
          <textarea
            v-model="statusModal.remark"
            rows="3"
            placeholder="เช่น อนุมัติตามสิทธิ์ หรือ ระบุเหตุผลที่ไม่อนุมัติ..."
            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none"
          ></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="statusModal.show = false" class="px-4 py-2 rounded-lg text-sm bg-slate-100 text-slate-600">ยกเลิก</button>
          <button
            @click="submitStatusUpdate"
            :class="statusModal.status === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
            class="px-4 py-2 rounded-lg text-sm text-white font-medium"
          >
            {{ statusModal.status === 'approved' ? 'ยืนยันอนุมัติ' : 'ยืนยันไม่อนุมัติ' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const todayStr = () => new Date().toISOString().split('T')[0];

export default {
  name: 'App',
  data() {
    return {
      currentUser: null,
      authTab: 'login',
      adminTab: 'leaves',
      toast: { show: false, message: '', success: true },
      loginForm: { username: '', password: '' },
      registerForm: { full_name: '', department: '', username: '', password: '', role: 'user' },
      appData: { leave_types: [], my_leaves: [], all_leaves: [], users: [] },
      createForm: {
        leave_type_id: '',
        start_date: todayStr(),
        end_date: todayStr(),
        leave_period: 'full',
        total_days: 1,
        reason: ''
      },
      adminFilter: { search: '', status: 'all', type: 'all' },
      typeForm: { id: '', name: '', max_days: '', description: '' },
      editModal: {
        show: false,
        form: {
          id: '',
          leave_type_id: '',
          start_date: '',
          end_date: '',
          leave_period: 'full',
          total_days: 1,
          reason: '',
          status: 'pending',
          admin_remark: ''
        }
      },
      statusModal: { show: false, id: '', status: '', remark: '' }
    };
  },
  computed: {
    activeLeaveTypes() {
      return this.appData.leave_types.filter(t => Number(t.is_active) === 1);
    },
    userQuotas() {
      return this.activeLeaveTypes.map(t => {
        const used = this.appData.my_leaves
          .filter(l => Number(l.leave_type_id) === Number(t.id) && l.status === 'approved')
          .reduce((sum, l) => sum + parseFloat(l.total_days), 0);
        const remain = Math.max(0, t.max_days - used);
        const pct = Math.min(100, (used / t.max_days) * 100);
        return { ...t, used, remain, pct };
      });
    },
    adminStats() {
      const list = this.appData.all_leaves;
      return {
        pending: list.filter(l => l.status === 'pending').length,
        approved: list.filter(l => l.status === 'approved').length,
        rejected: list.filter(l => l.status === 'rejected').length
      };
    },
    filteredAdminLeaves() {
      const q = this.adminFilter.search.toLowerCase();
      return this.appData.all_leaves.filter(l => {
        const matchSearch =
          l.user_name.toLowerCase().includes(q) ||
          l.department.toLowerCase().includes(q) ||
          l.reason.toLowerCase().includes(q);
        const matchStatus = this.adminFilter.status === 'all' || l.status === this.adminFilter.status;
        const matchType = this.adminFilter.type === 'all' || String(l.leave_type_id) === this.adminFilter.type;
        return matchSearch && matchStatus && matchType;
      });
    }
  },
  async apiCall(action, payload = null) {
      try {
        const options = payload
          ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }
          : { method: 'GET' };
        const res = await fetch(`api.php?action=${action}`, options);
        const text = await res.text();
        let json;
        try {
          json = JSON.parse(text);
        } catch (e) {
          console.error(`PHP Error [${action}]:`, text);
          const cleanMsg = text.replace(/<[^>]*>/g, '').trim();
          this.showToast(`PHP Error (${action}): ` + cleanMsg.slice(0, 120), false);
          return null;
        }
        if (!json.success) {
          this.showToast(json.message || 'เกิดข้อผิดพลาด', false);
          return null;
        }
        return json;
      } catch (err) {
        this.showToast('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ PHP ได้', false);
        return null;
      }
    },
  methods: {
    showToast(message, success = true) {
      this.toast = { show: true, message, success };
      setTimeout(() => {
        this.toast.show = false;
      }, 3200);
    },
    async apiCall(action, payload = null) {
      try {
        const options = payload
          ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }
          : { method: 'GET' };
        const res = await fetch(`api.php?action=${action}`, options);
        const json = await res.json();
        if (!json.success) {
          this.showToast(json.message || 'เกิดข้อผิดพลาด', false);
          return null;
        }
        return json;
      } catch (err) {
        this.showToast('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ PHP ได้', false);
        return null;
      }
    },
    async handleLogin() {
      const res = await this.apiCall('login', this.loginForm);
      if (!res) return;
      this.currentUser = res.data.user;
      this.showToast(res.message);
      await this.refreshData();
    },
    async quickLogin(username, password) {
      const res = await this.apiCall('login', { username, password });
      if (!res) return;
      this.currentUser = res.data.user;
      this.showToast(`เข้าสู่ระบบในชื่อ ${this.currentUser.full_name}`);
      await this.refreshData();
    },
    async handleRegister() {
      const res = await this.apiCall('register', this.registerForm);
      if (!res) return;
      this.currentUser = res.data.user;
      this.showToast(res.message);
      await this.refreshData();
    },
    async handleLogout() {
      const res = await this.apiCall('logout');
      if (!res) return;
      this.currentUser = null;
      this.showToast('ออกจากระบบเรียบร้อยแล้ว');
    },
    async refreshData() {
      const res = await this.apiCall('get_dashboard_data');
      if (!res) return;
      this.appData = res.data;
      if (this.activeLeaveTypes.length > 0 && !this.createForm.leave_type_id) {
        this.createForm.leave_type_id = this.activeLeaveTypes[0].id;
      }
    },
    calcDaysValue(startStr, endStr, period) {
      if (!startStr || !endStr) return 1;
      const start = new Date(startStr);
      const end = new Date(endStr);
      if (end < start) return period === 'full' ? 1 : 0.5;
      const diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
      return period === 'full' ? diff : diff === 1 ? 0.5 : diff - 0.5;
    },
    recalcCreateDays() {
      if (new Date(this.createForm.end_date) < new Date(this.createForm.start_date)) {
        this.createForm.end_date = this.createForm.start_date;
      }
      this.createForm.total_days = this.calcDaysValue(
        this.createForm.start_date,
        this.createForm.end_date,
        this.createForm.leave_period
      );
    },
    recalcEditDays() {
      if (new Date(this.editModal.form.end_date) < new Date(this.editModal.form.start_date)) {
        this.editModal.form.end_date = this.editModal.form.start_date;
      }
      this.editModal.form.total_days = this.calcDaysValue(
        this.editModal.form.start_date,
        this.editModal.form.end_date,
        this.editModal.form.leave_period
      );
    },
    async handleCreateLeave() {
      const res = await this.apiCall('create_leave', this.createForm);
      if (!res) return;
      this.createForm.reason = '';
      this.showToast(res.message);
      await this.refreshData();
    },
    openEditModal(leave) {
      this.editModal.form = {
        id: leave.id,
        leave_type_id: leave.leave_type_id,
        start_date: leave.start_date,
        end_date: leave.end_date,
        leave_period: leave.leave_period,
        total_days: leave.total_days,
        reason: leave.reason,
        status: leave.status,
        admin_remark: leave.admin_remark || ''
      };
      this.editModal.show = true;
    },
    async handleUpdateLeave() {
      const res = await this.apiCall('update_leave', this.editModal.form);
      if (!res) return;
      this.editModal.show = false;
      this.showToast(res.message);
      await this.refreshData();
    },
    async deleteLeave(id) {
      if (!confirm('ยืนยันการลบ/ยกเลิกรายการใบลานี้?')) return;
      const res = await this.apiCall('delete_leave', { id });
      if (!res) return;
      this.showToast(res.message);
      await this.refreshData();
    },
    openStatusModal(id, status) {
      this.statusModal = { show: true, id, status, remark: '' };
    },
    async submitStatusUpdate() {
      const res = await this.apiCall('update_leave_status', {
        id: this.statusModal.id,
        status: this.statusModal.status,
        admin_remark: this.statusModal.remark
      });
      if (!res) return;
      this.statusModal.show = false;
      this.showToast(res.message);
      await this.refreshData();
    },
    editLeaveType(t) {
      this.typeForm = { id: t.id, name: t.name, max_days: t.max_days, description: t.description || '' };
    },
    resetTypeForm() {
      this.typeForm = { id: '', name: '', max_days: '', description: '' };
    },
    async handleSaveLeaveType() {
      const res = await this.apiCall('save_leave_type', this.typeForm);
      if (!res) return;
      this.resetTypeForm();
      this.showToast(res.message);
      await this.refreshData();
    },
    async toggleLeaveType(id) {
      const res = await this.apiCall('toggle_leave_type', { id });
      if (!res) return;
      this.showToast(res.message);
      await this.refreshData();
    },
    async deleteLeaveType(id) {
      if (!confirm('ยืนยันการลบประเภทใบลานี้?')) return;
      const res = await this.apiCall('delete_leave_type', { id });
      if (!res) return;
      this.showToast(res.message);
      await this.refreshData();
    },
    async toggleUserRole(id) {
      const res = await this.apiCall('toggle_user_role', { id });
      if (!res) return;
      this.showToast(res.message);
      await this.refreshData();
    },
    statusLabel(status) {
      return { pending: 'รออนุมัติ', approved: 'อนุมัติแล้ว', rejected: 'ไม่อนุมัติ' }[status] || status;
    },
    statusBadgeClass(status) {
      return {
        pending: 'bg-amber-100 text-amber-700',
        approved: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-rose-100 text-rose-700'
      }[status] || '';
    },
    periodLabel(period) {
      return { full: 'เต็มวัน', morning: 'ครึ่งวันเช้า', afternoon: 'ครึ่งวันบ่าย' }[period] || period;
    }
  }
};
</script>