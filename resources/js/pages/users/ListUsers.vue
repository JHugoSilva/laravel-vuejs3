<script setup>
import { onMounted, ref, reactive, watch } from "vue";
import { Form, Field } from "vee-validate";
import { useToastr } from "../../toastr.js";
import { debounce } from "lodash";
import { Bootstrap4Pagination } from "laravel-vue-pagination";
import axios from "axios";
import * as yup from "yup";
import UserListItem from "./UserListItem.vue";

const toastr = useToastr();
const formValues = ref();
const users = ref({ data: [] });
const editing = ref(false);
const form = ref(null);
const selectAll = ref(false);
const searhQuery = ref(null);
const userIdBeingDeleted = ref(null);
const selectedUsers = ref([]);

const getUsers = (page = 1) => {
  axios
    .get(`/api/users?page=${page}`, { params: { query: searhQuery.value } })
    .then((response) => {
      users.value = response.data;
      selectedUsers.value = [];
      selectAll.value = false;
    });
};

const createUserSchema = yup.object({
  name: yup.string().required(),
  email: yup.string().email().required(),
  password: yup.string().required().min(8),
});

const editUserSchema = yup.object({
  name: yup.string().required(),
  email: yup.string().email().required(),
  password: yup.string().when((password, schema) => {
    if (password[0] != undefined) {
      return password ? schema.required().min(8) : schema;
    }
  }),
});

const createUser = (values, { resetForm, setErrors }) => {
  axios
    .post("/api/users", values)
    .then((response) => {
      users.value.data.unshift(response.data);
      $("#userFormModal").modal("hide");
      resetForm();
      toastr.success("User created successfully");
    })
    .catch((error) => {
      if (error.response.data.errors) {
        setErrors(error.response.data.errors);
      }
    });
};

const addUser = () => {
  editing.value = false;
  resetFormulario();
  $("#userFormModal").modal("show");
};

const editUser = (user) => {
  editing.value = true;
  form.value.resetForm();
  $("#userFormModal").modal("show");
  formValues.value = {
    id: user.id,
    name: user.name,
    email: user.email,
  };
};

const updateUser = (values, { setErrors }) => {
  axios
    .put("/api/users/" + formValues.value.id, values)
    .then((response) => {
      const index = users.value.findIndex((user) => user.id == response.data.users.id);
      users.value[index] = response.data.users;
      $("#userFormModal").modal("hide");
      toastr.success("User updated successfully");
    })
    .catch(() => {})
    .finally(() => {});
};

const handleSubmit = (values, actions) => {
  if (editing.value) {
    updateUser(values, actions);
  } else {
    createUser(values, actions);
  }
};

const confirmUserDeletion = (id) => {
  userIdBeingDeleted.value = id;
  $("#deleteUserModal").modal("show");
};

const deleteUser = () => {
  axios.delete(`/api/users/${userIdBeingDeleted.value}`).then(() => {
    $("#deleteUserModal").modal("hide");
    users.value.data = users.value.data.filter(
      (user) => user.id !== userIdBeingDeleted.value
    );
    toastr.success("User deleted successfully");
  });
};

const resetFormulario = () => {
  formValues.value = {
    id: "",
    name: "",
    email: "",
  };
};

const toggleSelection = (user) => {
  const index = selectedUsers.value.indexOf(user.id);
  if (index === -1) {
    selectedUsers.value.push(user.id);
  } else {
    selectedUsers.value.splice(index, 1);
  }
};

const bulkDelete = () => {
  axios.delete("/api/users", { data: { ids: selectedUsers.value } }).then((response) => {
    users.value.data = users.value.data.filter(
      (user) => !selectedUsers.value.includes(user.id)
    );
    selectedUsers.value = [];
    selectAll.value = false;
    toastr.success("Users deleted successfully!");
  });
};

const selectedAllUsers = () => {
  if (selectAll.value) {
    selectedUsers.value = users.value.data.map((user) => user.id);
  } else {
    selectedUsers.value = [];
  }
};

watch(
  searhQuery,
  debounce(() => {
    getUsers();
  }, 300)
);

onMounted(() => {
  getUsers();
});
</script>
<template>
  <div class="content-header">
    <div class="container-fluid">
      <div class="d-flex justify-content-between">
        <div class="d-flex">
          <button @click="addUser" type="button" class="btn btn-primary mb-2">
            <i class="fa fa-plus-circle mr-1"></i>
            Add New User
          </button>
        </div>
        <div v-if="selectedUsers.length > 0">
          <button @click="bulkDelete" type="button" class="btn btn-danger mb-2 ml-2 mr-2">
            <i class="fa fa-trash mr-1"></i>
            Delete Selected
          </button>
          <span>Selected {{ selectedUsers.length }} users</span>
        </div>
        <div>
          <input
            type="text"
            class="form-control"
            v-model="searhQuery"
            placeholder="Search..."
          />
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" v-model="selectAll" @change="selectedAllUsers" />
                </th>
                <th style="width: 10px">#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered Date</th>
                <th>Role</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody v-if="users.data.length > 0">
              <UserListItem
                v-for="(user, index) in users.data"
                :key="user.id"
                :user="user"
                :index="index"
                @edit-user="editUser"
                @confirm-user-deletion="confirmUserDeletion"
                @toggle-selection="toggleSelection"
                :select-all="selectAll"
              />
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="6" class="text-center">No results found...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <Bootstrap4Pagination :data="users" @pagination-change-page="getUsers" />
    </div>

    <div
      class="modal fade"
      id="userFormModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span v-if="editing">Edit User</span>
            <span v-else>Add New User</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <Form
              ref="form"
              @submit="handleSubmit"
              :validation-schema="editing ? editUserSchema : createUserSchema"
              v-slot="{ errors }"
              :initial-values="formValues"
            >
              <div class="form-group">
                <label for="name">Name</label>
                <Field
                  name="name"
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Enter name"
                  :class="{ 'is-invalid': errors.name }"
                />
                <span id="error">{{ errors.name }}</span>
              </div>

              <div class="form-group">
                <label for="email">Email</label>
                <Field
                  name="email"
                  type="email"
                  class="form-control"
                  id="email"
                  placeholder="Enter email"
                  :class="{ 'is-invalid': errors.email }"
                />
                <span id="error">{{ errors.email }}</span>
              </div>

              <div class="form-group">
                <label for="password">Password</label>
                <Field
                  name="password"
                  type="password"
                  class="form-control"
                  id="password"
                  placeholder="Enter password"
                  :class="{ 'is-invalid': errors.password }"
                />
                <span id="error">{{ errors.password }}</span>
              </div>

              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </Form>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="deleteUserModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span>Delete User</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <h5>Are you sure you want to delete this user ?</h5>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Cancel
            </button>
            <button @click.prevent="deleteUser" type="button" class="btn btn-primary">
              Delete User
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
#error {
  color: red;
}
</style>
