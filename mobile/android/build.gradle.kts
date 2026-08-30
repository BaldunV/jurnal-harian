allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")
}

// Plugins (audioplayers_android, rive_common) were published with compileSdk 33
// while their transitive AndroidX dependencies now require compileSdk 34+. Force
// the conflicting artifacts back to the last 33-compatible releases so the
// AAR metadata check passes. The host app still compiles against SDK 37.
subprojects {
    configurations.all {
        resolutionStrategy {
            force(
                "androidx.activity:activity:1.7.2",
                "androidx.annotation:annotation-experimental:1.3.0",
                "androidx.core:core:1.10.1",
                "androidx.core:core-ktx:1.10.1",
                "androidx.exifinterface:exifinterface:1.3.0",
                "androidx.fragment:fragment:1.6.2",
                "androidx.lifecycle:lifecycle-livedata:2.6.2",
                "androidx.lifecycle:lifecycle-livedata-core:2.6.2",
                "androidx.lifecycle:lifecycle-livedata-core-ktx:2.6.2",
                "androidx.lifecycle:lifecycle-process:2.6.2",
                "androidx.lifecycle:lifecycle-runtime:2.6.2",
                "androidx.lifecycle:lifecycle-viewmodel:2.6.2",
                "androidx.lifecycle:lifecycle-viewmodel-savedstate:2.6.2",
                "androidx.window:window:1.1.0",
                "androidx.window:window-java:1.1.0",
            )
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
