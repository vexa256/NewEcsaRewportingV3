
<div x-data="{
    searchQuery: '',
    clusters: @js($clusters),
    filteredClusters: @js($clusters),
    activeCluster: null,
    filterClusters() {
        this.filteredClusters = this.clusters.filter(cluster =>
            cluster.Cluster_Name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            cluster.Description.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    }
}" x-init="$nextTick(() => {
    $el.querySelectorAll('.gradient-bg').forEach(el => {
        el.classList.add('gradient-animate');
    });
})">

    <!-- Header Card -->
    <div class="card mb-5 mb-xxl-8">
        <div class="card-body p-0">
            <div class="position-relative overflow-hidden">
                <div class="gradient-bg position-absolute top-0 start-0 end-0 bottom-0"></div>
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between p-8">
                    <div class="d-flex align-items-center mb-4 mb-md-0">
                        <span class="svg-icon svg-icon-2hx text-white me-3">
                            <svg><use xlink:href="#svg-icon__cluster"/></svg>
                        </span>
                        <h1 class="text-white fs-1 fw-bolder mb-0">Cluster Target Management</h1>
                    </div>

                    @if ($hasInvalidClusters)
                    <div class="d-flex align-items-center" x-data="{ show: true }" x-show="show">
                        <div class="badge badge-light-danger fs-base d-flex align-items-center py-4 px-4">
                            <span class="svg-icon svg-icon-3 svg-icon-danger me-2">
                                <svg><use xlink:href="#svg-icon__warning"/></svg>
                            </span>
                            <span class="fw-bold">Configuration issues detected</span>
                            <button type="button" class="btn btn-icon btn-sm btn-active-icon-danger ms-3" @click="show = false">
                                <span class="svg-icon svg-icon-2">
                                    <svg><use xlink:href="#svg-icon__close"/></svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-5 mb-xxl-8">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-5">
                <!-- Search Input -->
                <div class="d-flex align-items-center position-relative flex-grow-1">
                    <span class="svg-icon svg-icon-2 position-absolute ms-4">
                        <svg><use xlink:href="#svg-icon__search"/></svg>
                    </span>
                    <input type="text"
                           class="form-control form-control-solid ps-12"
                           placeholder="Search clusters..."
                           x-model="searchQuery"
                           @input="filterClusters"
                           autocomplete="off">
                </div>

                <!-- Filters -->
                <div class="d-flex gap-4">
                    <button class="btn btn-flex btn-active-primary fw-bold">
                        <span class="svg-icon svg-icon-4 me-2">
                            <svg><use xlink:href="#svg-icon__filter"/></svg>
                        </span>
                        All Clusters
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($hasInvalidClusters)
    <!-- Alert -->
    <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-6 mb-5 mb-xxl-8" x-data="{ show: true }" x-show="show">
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h4 class="fw-bold fs-4 text-gray-800">Configuration Warning</h4>
            <span class="text-gray-600 fs-5">Some clusters require attention due to invalid configuration settings.</span>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon btn-sm btn-active-icon-danger" @click="show = false">
            <span class="svg-icon svg-icon-2">
                <svg><use xlink:href="#svg-icon__close"/></svg>
            </span>
        </button>
    </div>
    @endif

    <!-- Clusters Grid -->
    <template x-if="filteredClusters.length === 0">
        <div class="card">
            <div class="card-body p-20 text-center">
                <div class="text-center py-15">
                    <span class="svg-icon svg-icon-4x opacity-50">
                        <svg><use xlink:href="#svg-icon__search"/></svg>
                    </span>
                    <h3 class="fw-bold text-gray-600 mb-2">No clusters found</h3>
                    <span class="text-muted fs-4">Try adjusting your search criteria</span>
                </div>
            </div>
        </div>
    </template>

    <div class="row g-5 g-xxl-8" x-show="filteredClusters.length > 0">
        <template x-for="(cluster, index) in filteredClusters" :key="cluster.ClusterID">
            <div class="col-md-6 col-xxl-4">
                <div class="card card-flush h-md-100"
                     :class="{'bg-light-primary': activeCluster === cluster.ClusterID}"
                     @mouseenter="activeCluster = cluster.ClusterID"
                     @mouseleave="activeCluster = null">
                    <div class="card-header pt-6">
                        <div class="card-title">
                            <div class="symbol symbol-50px symbol-circle me-4">
                                <span class="symbol-label bg-primary text-white fs-2 fw-bolder">C</span>
                            </div>
                            <div class="d-flex flex-column">
                                <h3 class="fw-bolder text-gray-800 mb-0" x-text="cluster.Cluster_Name"></h3>
                                <span class="text-muted fw-bold" x-text="cluster.Description"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <form method="GET" :action="'{{ route('targets.setup') }}'">
                            <input type="hidden" name="ClusterID" :value="cluster.ClusterID">
                            <button type="submit" class="btn btn-primary w-100">
                                Manage Targets
                                <span class="svg-icon svg-icon-3 ms-2">
                                    <svg><use xlink:href="#svg-icon__arrow-right"/></svg>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0369a1, #0284c7, #0ea5e9, #38bdf8);
            background-size: 400% 400%;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .gradient-animate {
            opacity: 1;
            animation: gradientAnimation 15s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card-flush:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
</div>


<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
